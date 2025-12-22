<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
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

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ProductImport, $request->file('excel_file'));
            return back()->with('msg', 'Nhập dữ liệu Excel thành công!');
        } catch (\Exception $e) {
            return back()->with('warning', 'Lỗi file Excel: ' . $e->getMessage());
        }
    }
}
