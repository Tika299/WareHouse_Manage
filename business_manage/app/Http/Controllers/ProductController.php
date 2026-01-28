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

class ProductController extends Controller
{
    use Select2Searchable;
    public function index(Request $request)
    {
        // Khởi tạo query
        $query = Product::query();

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

    public function create()
    {
        return view('products.create', ['activeGroup' => 'inventory', 'activeName' => 'products']);
    }

    public function store(Request $request)
    {
        $request->validate(['sku' => 'required|unique:products', 'name' => 'required']);
        Product::create($request->all());
        return redirect()->route('products.index')->with('msg', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'), ['activeGroup' => 'inventory', 'activeName' => 'products']);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return redirect()->route('products.index')->with('msg', 'Cập nhật thành công!');
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
