<?php

namespace App\Http\Controllers;

use App\Models\{Product, Customer, CustomerReturn, CustomerReturnDetail, StockLog, CreditLog, SalesOrder, SalesDetail};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\CustomerReturn::with(['customer', 'user']);

        // Lọc theo khách hàng (nếu dùng AJAX search từ header hoặc link)
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Lọc theo khoảng ngày
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        // Lấy thông tin khách hàng đang được lọc (để hiển thị lại trên Select2 nếu có)
        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            $selectedCustomer = \App\Models\Customer::find($request->customer_id);
        }

        return view('sales.returns.index', [
            'returns' => $returns,
            'selectedCustomer' => $selectedCustomer,
            'activeGroup' => 'sales',
            'activeName' => 'customer_returns' // Khớp với Header của bạn
        ]);
    }

    public function create()
    {
        return view('sales.returns.create', [
            'activeGroup' => 'sales',
            'activeName' => 'customer_returns'
        ]);
    }

    /**
     * AJAX: Tìm kiếm đơn hàng để trả
     */
    public function searchOrdersAjax(Request $request)
    {
        $search = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $query = \App\Models\SalesOrder::with('customer');

        // Tìm theo mã đơn hoặc tên khách hàng
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                    ->orWhereHas('customer', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', "%$search%");
                    });
            });
        }

        $totalCount = (clone $query)->count();

        $orders = $query->latest()
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    // Text hiển thị trong danh sách Select2
                    'text' => "#DH" . str_pad($order->id, 5, '0', STR_PAD_LEFT) . " - " . $order->customer->name . " (" . number_format($order->total_final_amount) . "đ)",
                    // Dữ liệu phụ để JS dùng bắn vào bảng
                    'customer_name' => $order->customer->name
                ];
            });

        // BẮT BUỘC TRẢ VỀ ĐỦ kết quả và phân trang để Component không bị lỗi 'more'
        return response()->json([
            'results' => $orders,
            'pagination' => [
                'more' => ($offset + $perPage) < $totalCount
            ]
        ]);
    }

    /**
     * AJAX: Lấy sản phẩm từ đơn hàng đã chọn
     */
    public function getOrderDetails($id)
    {
        $order = \App\Models\SalesOrder::with('details.product')->find($id);

        if (!$order) return response()->json([]);

        return response()->json($order->details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.refund_price' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $order = SalesOrder::lockForUpdate()->findOrFail($request->sales_order_id);

                $soldQtyByProduct = SalesDetail::query()
                    ->where('sales_order_id', $order->id)
                    ->select('product_id', DB::raw('SUM(quantity) as sold_qty'))
                    ->groupBy('product_id')
                    ->pluck('sold_qty', 'product_id')
                    ->map(fn($qty) => (int) $qty)
                    ->all();

                $requestedQtyByProduct = [];
                foreach ($request->items as $index => $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];

                    if (!array_key_exists($productId, $soldQtyByProduct)) {
                        throw new \Exception(
                            "Dòng #" . ($index + 1) . ": product_id {$productId} không tồn tại trong chi tiết đơn hàng gốc."
                        );
                    }

                    $requestedQtyByProduct[$productId] = ($requestedQtyByProduct[$productId] ?? 0) + $quantity;

                    if ($requestedQtyByProduct[$productId] > $soldQtyByProduct[$productId]) {
                        throw new \Exception(
                            "Dòng #" . ($index + 1) . ": số lượng trả của product_id {$productId} vượt quá số lượng đã mua. " .
                                "Đã mua {$soldQtyByProduct[$productId]}, đang trả {$requestedQtyByProduct[$productId]}."
                        );
                    }
                }

                $return = CustomerReturn::create([
                    'customer_id' => $order->customer_id,
                    'sales_order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'total_return_value' => 0,
                    'note' => $request->note,
                ]);

                $totalReturnValue = 0;

                foreach ($request->items as $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];
                    $refundPrice = (float) $item['refund_price'];

                    $totalReturnValue += $quantity * $refundPrice;

                    CustomerReturnDetail::create([
                        'customer_return_id' => $return->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'refund_price' => $refundPrice,
                    ]);

                    $product = Product::lockForUpdate()->findOrFail($productId);
                    $newStockQty = $product->stock_quantity + $quantity;
                    $product->update(['stock_quantity' => $newStockQty]);

                    StockLog::create([
                        'product_id' => $product->id,
                        'ref_type' => 'import',
                        'ref_id' => $return->id,
                        'change_qty' => $quantity,
                        'final_qty' => $newStockQty,
                    ]);
                }

                $return->update(['total_return_value' => $totalReturnValue]);

                $customer = Customer::lockForUpdate()->findOrFail($order->customer_id);
                $oldDebt = $customer->total_debt;
                $newDebt = $oldDebt - $totalReturnValue;
                $customer->update(['total_debt' => $newDebt]);

                CreditLog::create([
                    'target_type' => 'customer',
                    'target_id' => $customer->id,
                    'ref_type' => 'voucher',
                    'ref_id' => $return->id,
                    'change_amount' => -$totalReturnValue,
                    'new_balance' => $newDebt,
                    'note' => 'Trả hàng từ đơn #DH' . $order->id,
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Đã nhận hàng trả và giảm nợ cho khách!',
                        'data' => $return,
                    ], 201);
                }

                return redirect()
                    ->route('customer_returns.index')
                    ->with('msg', 'Đã nhận hàng trả và giảm nợ cho khách!');
            });
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation hoặc xử lý hoàn hàng thất bại.',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
