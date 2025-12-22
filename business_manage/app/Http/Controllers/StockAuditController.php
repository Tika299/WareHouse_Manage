<?php

namespace App\Http\Controllers;

use App\Models\{Product, StockAudit, StockAuditDetail, StockLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAuditController extends Controller
{
    public function index()
    {
        $audits = StockAudit::with('user')->latest()->paginate(15);
        return view('inventory.audit.index', compact('audits'), ['activeGroup' => 'inventory', 'activeName' => 'audits']);
    }

    public function create()
    {
        $products = Product::all();
        return view('inventory.audit.create', compact('products'), ['activeGroup' => 'inventory', 'activeName' => 'audits']);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $totalDiffValue = 0;

            // 1. Tạo phiếu kiểm hàng chính
            $audit = StockAudit::create([
                'user_id' => auth()->id(),
                'note' => $request->note,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $diffQty = $item['actual_qty'] - $product->stock_quantity;
                $diffValue = $diffQty * $product->cost_price;
                $totalDiffValue += $diffValue;

                // 2. Lưu chi tiết phiếu kiểm
                StockAuditDetail::create([
                    'stock_audit_id' => $audit->id,
                    'product_id' => $product->id,
                    'system_qty' => $product->stock_quantity,
                    'actual_qty' => $item['actual_qty'],
                    'diff_qty' => $diffQty,
                    'cost_price' => $product->cost_price,
                ]);

                // 3. Cập nhật lại tồn kho thực tế cho sản phẩm
                if ($diffQty != 0) {
                    $product->update(['stock_quantity' => $item['actual_qty']]);

                    // 4. Ghi Thẻ kho (Stock Log)
                    StockLog::create([
                        'product_id' => $product->id,
                        'ref_type' => 'audit',
                        'ref_id' => $audit->id,
                        'change_qty' => $diffQty,
                        'final_qty' => $item['actual_qty']
                    ]);
                }
            }

            // Cập nhật tổng giá trị chênh lệch cho phiếu
            $audit->update(['total_diff_value' => $totalDiffValue]);

            return redirect()->route('audits.index')->with('msg', 'Kiểm kho hoàn tất. Tồn kho đã được điều chỉnh!');
        });
    }
}
