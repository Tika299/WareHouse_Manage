<?php

namespace App\Http\Controllers;

use App\Exports\ProductTemplateExport;
use App\Imports\ProductImport;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\Select2Searchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use Select2Searchable;
    public function index(Request $request)
    {
        // Lấy dữ liệu cho các ô Select lọc
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        // Query mặc định: Chỉ lấy sản phẩm CHA, kèm theo các quan hệ
        $query = Product::whereNull('parent_id')->with(['variants', 'category', 'brand']);

        // 1. Tìm kiếm theo Tên hoặc SKU (Tìm xuyên thấu xuống cả SKU con)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhereHas('variants', function ($sq) use ($search) {
                        $sq->where('sku', 'like', "%$search%");
                    });
            });
        }

        // 2. Lọc theo Ngành hàng (Bảng riêng)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Lọc theo Thương hiệu (Bảng riêng)
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // 4. Lọc theo Trạng thái kho (Logic tồn > 0)
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status == 'in_stock') {
                $query->where(function ($q) {
                    $q->where('stock_quantity', '>', 0)
                        ->orWhereHas('variants', function ($sq) {
                            $sq->where('stock_quantity', '>', 0);
                        });
                });
            } elseif ($status == 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where('stock_quantity', '<=', 0)
                        ->where(function ($sq) {
                            $sq->whereDoesntHave('variants')
                                ->orWhereHas('variants', function ($ssq) {
                                    $ssq->selectRaw('SUM(stock_quantity)')->havingRaw('SUM(stock_quantity) <= 0');
                                });
                        });
                });
            } elseif ($status == 'low_stock') {
                $query->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereRaw('stock_quantity <= min_stock AND stock_quantity > 0')->whereDoesntHave('variants');
                    })->orWhereHas('variants', function ($vq) {
                        $vq->whereRaw('stock_quantity <= min_stock AND stock_quantity > 0');
                    });
                });
            }
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('products.index', compact('products', 'categories', 'brands'), [
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('products.create', compact('categories', 'brands'), [
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validate các trường cơ bản
        $request->validate([
            'name' => 'required|max:255',
            'is_variable' => 'required|in:0,1,2',
        ], [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
        ]);

        return DB::transaction(function () use ($request) {

            // 2. Xử lý Thêm nhanh hoặc Lấy ID của Category và Brand
            $categoryId = $this->handleMetaData(\App\Models\Category::class, $request->category_id);
            $brandId = $this->handleMetaData(\App\Models\Brand::class, $request->brand_id);

            // 3. Chuẩn bị dữ liệu chung
            $commonData = [
                'name'              => $request->name,
                'category_id'       => $categoryId,
                'brand_id'          => $brandId,
                'unit'              => $request->unit,
                'description'       => $request->description,
                'factor_retail'     => $request->factor_retail ?? 1.5,
                'factor_wholesale'  => $request->factor_wholesale ?? 1.1,
                'factor_ctv'        => $request->factor_ctv ?? 1.2,
                'factor_eco_margin' => $request->factor_eco_margin ?? 0.5,
                'factor_eco_fee'    => $request->factor_eco_fee ?? 0.3,
            ];

            // --- TRƯỜNG HỢP 0: SẢN PHẨM ĐƠN LẺ ---
            if ($request->is_variable == '0') {
                $singleData = array_merge($commonData, [
                    'sku'                    => $request->sku ?: 'SP-' . strtoupper(Str::random(8)),
                    'cost_price'             => $request->cost_price ?: 0,
                    'stock_quantity'         => $request->stock_quantity ?: 0,
                    'min_stock'              => $request->min_stock ?: 5,
                    'manual_retail_price'    => $request->manual_retail_price,
                    'manual_wholesale_price' => $request->manual_wholesale_price,
                    'manual_ctv_price'       => $request->manual_ctv_price,
                    'manual_ecommerce_price' => $request->manual_ecommerce_price,
                    'is_combo'               => false,
                ]);
                Product::create($singleData);
            }

            // --- TRƯỜNG HỢP 1: SẢN PHẨM CÓ BIẾN THỂ (CHA - CON) ---
            elseif ($request->is_variable == '1') {
                // Tạo sản phẩm CHA (không giữ kho trực tiếp)
                $parent = Product::create(array_merge($commonData, [
                    'sku'            => $request->sku ?: 'P-' . strtoupper(Str::random(8)),
                    'stock_quantity' => 0,
                    'cost_price'     => 0,
                    'is_combo'       => false,
                ]));

                // Tạo các biến thể CON
                if ($request->has('variants')) {
                    foreach ($request->variants as $v) {
                        if (!empty($v['variant_label'])) {
                            Product::create(array_merge($commonData, [
                                'parent_id'              => $parent->id,
                                'variant_label'          => $v['variant_label'],
                                'sku'                    => $v['sku'] ?: 'SKU-' . strtoupper(Str::random(8)),
                                'cost_price'             => $v['cost_price'] ?: 0,
                                'stock_quantity'         => $v['stock_quantity'] ?: 0,
                                'min_stock'              => $v['min_stock'] ?? 5,
                                'manual_retail_price'    => $v['manual_retail_price'] ?? null,
                                'manual_wholesale_price' => $v['manual_wholesale_price'] ?? null,
                            ]));
                        }
                    }
                }
            }

            // --- TRƯỜNG HỢP 2: SẢN PHẨM COMBO ---
            elseif ($request->is_variable == '2') {
                // Tạo bản ghi COMBO (Tồn kho sẽ được tính ảo từ Accessor)
                $combo = Product::create(array_merge($commonData, [
                    'sku'                    => $request->sku ?: 'CB-' . strtoupper(Str::random(8)),
                    'is_combo'               => true, // Đánh dấu là Combo
                    'stock_quantity'         => 0,    // Mặc định 0, tính toán qua Accessor
                    'cost_price'             => $request->cost_price ?: 0,
                    'manual_retail_price'    => $request->manual_retail_price,
                    'manual_wholesale_price' => $request->manual_wholesale_price,
                ]));

                // Lưu các thành phần cấu tạo Combo
                if ($request->has('combo_items')) {
                    foreach ($request->combo_items as $item) {
                        if (!empty($item['product_id'])) {
                            \App\Models\ComboItem::create([
                                'combo_id'   => $combo->id,
                                'product_id' => $item['product_id'],
                                'quantity'   => $item['quantity'] ?: 1,
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('products.index')->with('msg', 'Đã thêm sản phẩm mới thành công!');
        });
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('products.edit', compact('product', 'categories', 'brands'), [
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    /**
     * Cập nhật sản phẩm (Full Logic Chuyển đổi + Đồng bộ)
     */
    public function update(Request $request, Product $product)
    {
        return DB::transaction(function () use ($request, $product) {

            // 1. Xử lý Category & Brand (Nếu người dùng nhập tên mới)
            $categoryId = $this->handleMetaData(Category::class, $request->category_id);
            $brandId = $this->handleMetaData(Brand::class, $request->brand_id);

            // --- TRƯỜNG HỢP A: THU GỌN VỀ ĐƠN LẺ ---
            if ($request->convert_to_single == '1') {
                $product->variants()->delete();
                $data = $request->only([
                    'name',
                    'unit',
                    'description',
                    'sku',
                    'cost_price',
                    'stock_quantity',
                    'factor_retail',
                    'factor_wholesale',
                    'factor_ctv',
                    'factor_eco_margin',
                    'factor_eco_fee',
                    'manual_retail_price',
                    'manual_wholesale_price',
                    'manual_ctv_price',
                    'manual_ecommerce_price'
                ]);
                $data['category_id'] = $categoryId;
                $data['brand_id'] = $brandId;
                $data['parent_id'] = null;
                $product->update($data);
                return redirect()->route('products.index')->with('msg', 'Đã thu gọn về sản phẩm đơn lẻ!');
            }

            // --- TRƯỜNG HỢP B: CẬP NHẬT HOẶC CHUYỂN SANG BIẾN THỂ ---
            $parentData = $request->only([
                'name',
                'unit',
                'description',
                'factor_retail',
                'factor_wholesale',
                'factor_ctv',
                'factor_eco_margin',
                'factor_eco_fee',
                'manual_retail_price',
                'manual_wholesale_price',
                'manual_ctv_price',
                'manual_ecommerce_price'
            ]);
            $parentData['category_id'] = $categoryId;
            $parentData['brand_id'] = $brandId;

            // Xử lý SKU & Kho khi có biến thể mới
            if ($request->has('new_variants') && $product->variants->isEmpty()) {
                foreach ($request->new_variants as $nv) {
                    if ($nv['sku'] == $product->sku) {
                        $product->sku = $product->sku . '-P';
                        $product->save();
                        break;
                    }
                }
                $parentData['sku'] = $product->sku;
                $parentData['stock_quantity'] = 0;
                $parentData['cost_price'] = 0;
            } else {
                $parentData['sku'] = $request->sku;
                $parentData['cost_price'] = $request->cost_price;
                $parentData['stock_quantity'] = $request->stock_quantity;
            }

            $product->update($parentData);

            // ĐỒNG BỘ XUỐNG BIẾN THỂ CŨ
            if ($request->has('variants')) {
                foreach ($request->variants as $id => $vData) {
                    $variant = Product::findOrFail($id);
                    $variant->update([
                        'name'          => $product->name,
                        'category_id'   => $product->category_id,
                        'brand_id'      => $product->brand_id,
                        'unit'          => $product->unit,
                        'variant_label' => $vData['variant_label'],
                        'sku'           => $vData['sku'],
                        'cost_price'    => $vData['cost_price'],
                        'stock_quantity' => $vData['stock_quantity'],
                        'manual_retail_price' => $vData['manual_retail_price'],
                        'factor_retail'    => $product->factor_retail,
                        'factor_wholesale' => $product->factor_wholesale,
                        'factor_ctv'       => $product->factor_ctv,
                        'factor_eco_margin' => $product->factor_eco_margin,
                        'factor_eco_fee'   => $product->factor_eco_fee,
                    ]);
                }
            }

            // TẠO BIẾN THỂ MỚI
            if ($request->has('new_variants')) {
                foreach ($request->new_variants as $nv) {
                    if (!empty($nv['variant_label'])) {
                        Product::create(array_merge($nv, [
                            'parent_id'     => $product->id,
                            'name'          => $product->name,
                            'category_id'   => $product->category_id,
                            'brand_id'      => $product->brand_id,
                            'unit'          => $product->unit,
                            'factor_retail'    => $product->factor_retail,
                            'factor_wholesale' => $product->factor_wholesale,
                            'factor_ctv'       => $product->factor_ctv,
                            'factor_eco_margin' => $product->factor_eco_margin,
                            'factor_eco_fee'   => $product->factor_eco_fee,
                        ]));
                    }
                }
            }

            return redirect()->route('products.index')->with('msg', 'Cập nhật thành công!');
        });
    }

    public function destroy(Product $product)
    {
        if ($product->stock_quantity > 0 || $product->variants()->where('stock_quantity', '>', 0)->exists()) {
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
        // Chỉ lọc P- khi tìm sản phẩm
        $extraFilter = function ($query) {
            $query->where('sku', 'NOT LIKE', 'P-%');
        };

        return $this->performSelect2Search(
            $request,
            \App\Models\Product::class,
            ['name', 'sku'],
            $extraFilter
        );
    }

    /**
     * Hàm hỗ trợ xử lý Metadata (Lấy ID hoặc tạo mới nếu là text)
     */
    private function handleMetaData($model, $value)
    {
        if (empty($value)) return null;
        // Nếu là số, coi như là ID đã có sẵn
        if (is_numeric($value)) return $value;

        // Nếu là chữ, tạo mới bản ghi (Select2 Tags)
        $record = $model::firstOrCreate(['name' => trim($value)]);
        return $record->id;
    }

    // 1. Giao diện tạo Combo
    public function createCombo()
    {
        $categories = Category::all();
        $brands = Brand::all();

        // Chỉ lấy sản phẩm lẻ hoặc biến thể con để làm thành phần
        // Không lấy những sản phẩm bản thân nó đã là combo
        $components = Product::where('is_combo', false)
            ->where(function ($query) {
                $query->whereNull('parent_id') // Sản phẩm đơn lẻ
                    ->orWhereNotNull('parent_id'); // Hoặc biến thể con
            })->get();

        return view('products.create_combo', compact('categories', 'brands', 'components'), [
            'activeGroup' => 'inventory',
            'activeName' => 'products'
        ]);
    }

    // 2. Lưu Combo
    public function storeCombo(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'combo_items' => 'required|array|min:1',
        ], [
            'combo_items.required' => 'Bạn phải chọn ít nhất 1 sản phẩm thành phần.'
        ]);

        return DB::transaction(function () use ($request) {
            // Xử lý Category/Brand (thêm nhanh nếu gõ mới)
            $categoryId = $this->handleMetaData(Category::class, $request->category_id);
            $brandId = $this->handleMetaData(Brand::class, $request->brand_id);

            // Tạo sản phẩm Combo
            $combo = Product::create([
                'is_combo'               => true,
                'name'                   => $request->name,
                'category_id'            => $categoryId,
                'brand_id'               => $brandId,
                'unit'                   => $request->unit ?: 'Bộ',
                'sku'                    => $request->sku ?: 'CB-' . strtoupper(Str::random(8)),
                'description'            => $request->description,
                'cost_price'             => $request->cost_price ?: 0,
                'manual_retail_price'    => $request->manual_retail_price,
                'manual_wholesale_price' => $request->manual_wholesale_price,
                'manual_ctv_price'       => $request->manual_ctv_price,
                'manual_ecommerce_price' => $request->manual_ecommerce_price,
                'stock_quantity'         => 0, // Tồn kho sẽ tính qua Accessor
                'factor_retail'          => $request->factor_retail ?? 1.5,
                'factor_wholesale'       => $request->factor_wholesale ?? 1.1,
                // ... các hệ số khác
            ]);

            // Lưu công thức cấu tạo
            foreach ($request->combo_items as $item) {
                \App\Models\ComboItem::create([
                    'combo_id'   => $combo->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }

            return redirect()->route('products.index')->with('msg', 'Đã tạo bộ Combo thành công!');
        });
    }
}
