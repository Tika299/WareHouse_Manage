<?php

namespace App\Http\Controllers;

use App\Models\{Product, Customer, CustomerReturn, CustomerReturnDetail, StockLog, CreditLog, SalesOrder};
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
            'items' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            $order = SalesOrder::findOrFail($request->sales_order_id);
            $totalReturnValue = 0;

            // 1. Tạo phiếu trả hàng
            $return = CustomerReturn::create([
                'customer_id' => $order->customer_id,
                'sales_order_id' => $order->id,
                'user_id' => auth()->id(),
                'total_return_value' => 0, // Sẽ cập nhật sau
                'note' => $request->note
            ]);

            foreach ($request->items as $item) {
                if ($item['quantity'] <= 0) continue;

                $totalReturnValue += $item['quantity'] * $item['refund_price'];

                // 2. Lưu chi tiết
                CustomerReturnDetail::create([
                    'customer_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'refund_price' => $item['refund_price'],
                ]);

                // 3. Tăng tồn kho & Thẻ kho
                $product = Product::find($item['product_id']);
                $product->increment('stock_quantity', $item['quantity']);

                StockLog::create([
                    'product_id' => $product->id,
                    'ref_type' => 'import',
                    'ref_id' => $return->id,
                    'change_qty' => $item['quantity'],
                    'final_qty' => $product->stock_quantity
                ]);
            }

            $return->update(['total_return_value' => $totalReturnValue]);

            // 4. Giảm nợ gộp khách hàng
            $customer = $order->customer;
            $oldDebt = $customer->total_debt;
            $customer->decrement('total_debt', $totalReturnValue);

            CreditLog::create([
                'target_type' => 'customer',
                'target_id' => $customer->id,
                'ref_type' => 'voucher',
                'ref_id' => $return->id,
                'change_amount' => -$totalReturnValue,
                'new_balance' => $oldDebt - $totalReturnValue,
                'note' => 'Trả hàng từ đơn #DH' . $order->id
            ]);

            return redirect()->route('customer_returns.index')->with('msg', 'Đã nhận hàng trả và giảm nợ cho khách!');
        });
    }
}
