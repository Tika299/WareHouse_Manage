<?php

namespace App\Http\Controllers;

use App\Models\{Product, StockAudit, StockAuditDetail, StockLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAudit::with('user');

        // 1. Tìm kiếm theo mã phiếu
        if ($request->filled('search')) {
            $search = $request->input('search');
            $searchId = preg_replace('/[^0-9]/', '', $search); // Lấy phần số từ chuỗi tìm kiếm
            $query->where('id', $searchId);
        }

        // 2. Lọc theo ngày tháng (sửa typo 'form_date' thành 'from_date' nếu là lỗi, giả sử view dùng 'from_date')
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        $audits = $query->latest()->paginate(15);
        $audits->appends($request->all());
        return view('inventory.audit.index', compact('audits'), ['activeGroup' => 'inventory', 'activeName' => 'audits']);
    }

    public function create()
    {
        // Không load tất cả products nữa, vì dùng Ajax search
        return view('inventory.audit.create', ['activeGroup' => 'inventory', 'activeName' => 'audits']);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('search'); // Từ khóa từ Select2 (params.term)

        if (empty($query)) {
            return response()->json(['results' => []]);;
        }

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->limit(50) // Giới hạn kết quả để tránh tải nặng
            ->get(['id', 'name', 'sku', 'stock_quantity', 'cost_price']);

        // Format cho Select2
        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => $product->sku . ' - ' . $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock_quantity,
                'cost' => $product->cost_price,
            ];
        });

        return response()->json(['results' => $results]);
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
