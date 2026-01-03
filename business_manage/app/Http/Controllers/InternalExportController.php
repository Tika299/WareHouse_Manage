<?php

namespace App\Http\Controllers;

use App\Models\{Product, InternalExport, InternalExportDetail, StockLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalExportController extends Controller
{
    public function index()
    {
        $exports = InternalExport::with('user')->latest()->paginate(15);
        return view('inventory.internal_exports.index', compact('exports'), [
            'activeGroup' => 'inventory',
            'activeName' => 'internal_exports'
        ]);
    }

    public function create()
    {
        $products = Product::where('stock_quantity', '>', 0)->get();
        return view('inventory.internal_exports.create', compact('products'), [
            'activeGroup' => 'inventory',
            'activeName' => 'internal_exports'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason_type' => 'required',
            'items' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            $totalCostValue = 0;

            // 1. Tạo phiếu xuất chính
            $export = InternalExport::create([
                'user_id' => auth()->id(),
                'reason_type' => $request->reason_type,
                'note' => $request->note,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ tồn kho!");
                }

                $itemCostValue = $item['quantity'] * $product->cost_price;
                $totalCostValue += $itemCostValue;

                // 2. Lưu chi tiết xuất
                InternalExportDetail::create([
                    'internal_export_id' => $export->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'cost_price' => $product->cost_price,
                ]);

                // 3. Trừ kho & Ghi thẻ kho (Log)
                $newQty = $product->stock_quantity - $item['quantity'];
                $product->update(['stock_quantity' => $newQty]);

                StockLog::create([
                    'product_id' => $product->id,
                    'ref_type' => 'internal_export',
                    'ref_id' => $export->id,
                    'change_qty' => -$item['quantity'],
                    'final_qty' => $newQty
                ]);
            }

            $export->update(['total_cost_value' => $totalCostValue]);

            return redirect()->route('internal_exports.index')->with('msg', 'Đã xuất kho nội bộ thành công!');
        });
    }

    public function show($id)
    {
        // Load phiếu xuất kèm thông tin người lập và chi tiết sản phẩm (bao gồm thông tin sản phẩm đó)
        $export = \App\Models\InternalExport::with(['user', 'details.product'])->findOrFail($id);

        return view('inventory.internal_exports.show', [
            'export' => $export,
            'activeGroup' => 'inventory',
            'activeName' => 'internal_exports'
        ]);
    }
}
