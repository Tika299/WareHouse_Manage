<?php

namespace App\Http\Controllers;

use App\Exports\ProductTemplateExport;
use App\Imports\ProductImport;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\Select2Searchable;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use Select2Searchable;
    public function index(Request $request)
    {
        // Khởi tạo query
        $query = Product::whereNull('parent_id')->with('variants');

        //1. Tìm kiếm theo Tên hoặc SKU
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        //2. Lọc theo trạng thái tồn kho
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');

            if ($status == 'low_stock') {
                // Sắp hết hàng: tồn <= định mức tối thiểu
                $query->whereRaw('stock_quantity <= min_stock')
                    ->where('stock_quantity', '>', 0);
            } elseif ($status == 'out_of_stock') {
                // Hết hàng: tồn <= 0
                $query->where('stock_quantity', '<=', 0);
            } elseif ($status == 'in_stock') {
                // Còn hàng: tồn > định mức tối thiểu
                $query->whereRaw('stock_quantity > min_stock');
            }
        }

        //Sắp xếp mới nhất và phân trang
        $products = $query->latest()->paginate(20);

        //Giữ lại các tham số lọc khi chuyển trang (Pagination)
        $products->appends($request->all());

        return view('products.index', [
            'products' => $products,
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    public function create(Request $request)
    {
        $parent = null;
        // Nếu có parent_id trên URL, lấy thông tin cha để khóa tên và ngành hàng
        if ($request->has('parent_id')) {
            $parent = Product::findOrFail($request->parent_id);
        }

        return view('products.create', compact('parent'), [
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // Lấy dữ liệu chung (bao gồm product_type của bạn là Mỹ phẩm...)
            $commonData = $request->only([
                'name',
                'unit',
                'product_type',
                'description',
                'factor_retail',
                'factor_wholesale',
                'factor_ctv',
                'factor_eco_margin',
                'factor_eco_fee'
            ]);

            if ($request->has('parent_id')) {
                $parent = Product::findOrFail($request->parent_id);

                Product::create([
                    'parent_id'     => $parent->id,
                    'product_type'  => 'variant',
                    'name'          => $parent->name,
                    'product_type'  => $parent->product_type,
                    'variant_label' => $request->variant_label, // Lấy từ ô nhập đơn lẻ
                    'sku'           => $request->sku,
                    'cost_price'    => $request->cost_price ?? 0,
                    'stock_quantity' => $request->stock_quantity ?? 0,
                    'unit'          => $parent->unit,
                    'factor_retail' => $parent->factor_retail,
                    // ... copy các hệ số khác từ cha ...
                ]);
                return redirect()->route('products.index')->with('msg', 'Đã thêm biến thể mới!');
            }

            if ($request->is_variable == "0") {
                // TẠO SẢN PHẨM ĐƠN LẺ
                $data = array_merge($commonData, $request->only([
                    'sku',
                    'cost_price',
                    'stock_quantity',
                    'manual_retail_price',
                    'manual_wholesale_price',
                    'manual_ctv_price',
                    'manual_ecommerce_price'
                ]));
                Product::create($data);
            } else {
                // TẠO SẢN PHẨM CHA VÀ CÁC BIẾN THỂ CON
                $parent = Product::create(array_merge($commonData, ['sku' => 'P-' . time()]));

                if ($request->has('variants')) {
                    foreach ($request->variants as $v) {
                        Product::create(array_merge($commonData, [
                            'parent_id'     => $parent->id,
                            'variant_label' => $v['variant_label'],
                            'sku'           => $v['sku'],
                            'cost_price'    => $v['cost_price'] ?? 0,
                            'stock_quantity' => $v['stock_quantity'] ?? 0,
                            'manual_retail_price'    => $v['manual_retail_price'],
                            'manual_wholesale_price' => $v['manual_wholesale_price'],
                            'manual_ctv_price'       => $v['manual_ctv_price'],
                            'manual_ecommerce_price' => $v['manual_ecommerce_price'],
                        ]));
                    }
                }
            }
            return redirect()->route('products.index')->with('msg', 'Đã lưu sản phẩm!');
        });
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'), ['activeGroup' => 'inventory', 'activeName' => 'products']);
    }

    public function update(Request $request, Product $product)
    {
        return DB::transaction(function () use ($request, $product) {
            // 1. Cập nhật thông tin sản phẩm chính (Cha)
            $product->update($request->only(['name', 'product_type', 'description', 'factor_retail', 'factor_wholesale', 'factor_ctv', 'factor_eco_margin', 'factor_eco_fee']));

            // 2. Cập nhật các biến thể cũ (Nếu form gửi mảng variants[id][...])
            if ($request->has('variants')) {
                foreach ($request->variants as $id => $vData) {
                    $variant = Product::findOrFail($id);
                    $variant->update($vData);
                }
            }

            // 3. Tạo các biến thể MỚI (Nếu có dùng nút "Thêm dòng" trong trang Edit)
            if ($request->has('new_variants')) {
                foreach ($request->new_variants as $nv) {
                    Product::create(array_merge($nv, [
                        'parent_id' => $product->id,
                        'product_type' => 'variant',
                        'name' => $product->name,
                        'unit' => $product->unit,
                        'factor_retail' => $product->factor_retail,
                        'factor_wholesale' => $product->factor_wholesale,
                        'factor_ctv' => $product->factor_ctv,
                        'factor_eco_margin' => $product->factor_eco_margin,
                        'factor_eco_fee' => $product->factor_eco_fee,
                    ]));
                }
            }

            return redirect()->route('products.index')->with('msg', 'Cập nhật sản phẩm và biến thể thành công!');
        });
    }

    public function destroy(Product $product)
    {
        if ($product->stock_quantity > 0) {
            return back()->with('warning', 'Không thể xóa sản phẩm còn tồn kho!');
        }
        $product->delete();
        return back()->with('msg', 'Đã xóa sản phẩm!');
    }

    public function show($id)
    {
        // Nếu lỡ rơi vào đây, quay về trang danh sách luôn
        return redirect()->route('products.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('excel_file'));

            // Sử dụng 'msg' để khớp với header của bạn
            return redirect()->route('products.index')
                ->with('msg', 'Nhập danh sách sản phẩm từ Excel thành công!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                // Lấy lỗi đầu tiên của từng dòng
                $errorMessages[] = "Dòng " . $failure->row() . ": " . $failure->errors()[0];
            }

            // Chuyển mảng lỗi thành chuỗi có xuống dòng để hiển thị
            return redirect()->back()
                ->with('warning', 'Import thất bại! <br>' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('warning', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'mau_nhap_san_pham.xlsx');
    }

    public function searchAjax(Request $request)
    {
        return $this->performSelect2Search($request, \App\Models\Product::class, ['name', 'sku']);
    }
}
