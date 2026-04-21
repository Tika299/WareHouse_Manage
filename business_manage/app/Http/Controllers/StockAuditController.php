<?php

namespace App\Http\Controllers;

use App\Models\{Product, StockAudit, StockAuditDetail, StockLog};
use App\Traits\Select2Searchable; // Thêm trait này
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAuditController extends Controller
{
    use Select2Searchable; // Sử dụng trait dùng chung

    public function index(Request $request)
    {
        $query = StockAudit::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $searchId = preg_replace('/[^0-9]/', '', $search);
            $query->where('id', $searchId);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $audits = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.audit.index', compact('audits'), [
            'activeGroup' => 'inventory',
            'activeName' => 'audits'
        ]);
    }

    public function create()
    {
        return view('inventory.audit.create', [
            'activeGroup' => 'inventory',
            'activeName' => 'audits'
        ]);
    }

    /**
     * AJAX tìm kiếm sản phẩm cho kiểm kho
     * Phải lọc: Chỉ lấy 'single' hoặc 'variant'
     */
    public function searchProducts(Request $request)
    {
        // Select2 gửi từ khóa qua tham số 'q'
        $search = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $query = \App\Models\Product::query();

        // LOGIC FIX LỖI: 
        // Loại bỏ sản phẩm CHA (Sản phẩm cha trong hệ thống của bạn có SKU bắt đầu bằng P-)
        $query->where('sku', 'NOT LIKE', 'P-%');

        // Tìm kiếm theo Tên, SKU hoặc Nhãn biến thể
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('variant_label', 'LIKE', "%{$search}%");
            });
        }

        $totalCount = (clone $query)->count();

        $items = $query->limit($perPage)->offset($offset)->get()->map(function ($item) {
            // Tên đầy đủ: Nếu là biến thể thì hiện "Tên - Dung tích", nếu đơn lẻ thì hiện "Tên"
            $fullName = $item->name . ($item->variant_label ? ' - ' . $item->variant_label : '');

            return [
                'id'    => $item->id,
                'text'  => "[" . $item->sku . "] " . $fullName . " (Tồn: " . $item->stock_quantity . ")",
                'sku'   => $item->sku,
                'name'  => $fullName,
                'stock' => $item->stock_quantity,
                'cost_price' => $item->cost_price,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => ($offset + $perPage) < $totalCount
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        return DB::transaction(function () use ($request) {
            $totalDiffValue = 0;

            $audit = StockAudit::create([
                'user_id' => auth()->id(),
                'note'    => $request->note,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if (!$product) continue;

                $diffQty = $item['actual_qty'] - $product->stock_quantity;
                $diffValue = $diffQty * $product->cost_price;
                $totalDiffValue += $diffValue;

                // Lưu chi tiết
                StockAuditDetail::create([
                    'stock_audit_id' => $audit->id,
                    'product_id'     => $product->id,
                    'system_qty'     => $product->stock_quantity,
                    'actual_qty'     => $item['actual_qty'],
                    'diff_qty'       => $diffQty,
                    'cost_price'     => $product->cost_price,
                ]);

                // Cập nhật kho
                if ($diffQty != 0) {
                    $product->update(['stock_quantity' => $item['actual_qty']]);

                    StockLog::create([
                        'product_id' => $product->id,
                        'ref_type'   => 'audit',
                        'ref_id'     => $audit->id,
                        'change_qty' => $diffQty,
                        'final_qty'  => $item['actual_qty']
                    ]);
                }
            }

            $audit->update(['total_diff_value' => $totalDiffValue]);

            return redirect()->route('audits.index')->with('msg', 'Đã chốt phiếu kiểm kê và cân bằng kho thành công!');
        });
    }
}
