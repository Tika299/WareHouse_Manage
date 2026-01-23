<?php

namespace App\Http\Controllers;

use App\Models\{Product, PurchaseOrder, PurchaseDetail, Account, Supplier, CreditLog, StockLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        //1. Tìm kiếm theo mã phiếu
        if ($request->filled('search')) {
            $search = $request->input('search');
            //Loại bỏ chữ #PN
            $searchId = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($searchId) {
                $q->where('id', $searchId);
            });
        }

        //2. Lọc theo nhà cung cấp
        $query->when($request->supplier_id, function ($q) {
            return $q->where('supplier_id', request('supplier_id'));
        });

        //3. Lọc theo Trạng thái thanh toán
        if ($request->filled('status')) {
            if ($request->status == 'paid') {
                $query->whereColumn('paid_amount', '>=', 'total_final_amount');
            } elseif ($request->status == 'debt') {
                $query->whereColumn('paid_amount', '<', 'total_final_amount');
            }
        }

        // 4. Lọc theo Khoảng ngày (Từ ngày - Đến ngày)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Thực hiện truy vấn và phân trang
        $orders = $query->latest()->paginate(15);

        // Giữ lại các tham số lọc khi nhấn sang trang 2, 3...
        $orders->appends($request->all());

        // Lấy danh sách NCC để đổ vào dropdown lọc
        $suppliers = \App\Models\Supplier::select('id', 'name')->get();

        return view('imports.index', [
            'orders' => $orders,
            'suppliers' => $suppliers,
            'activeGroup' => 'inventory',
            'activeName' => 'imports'
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        $accounts = Account::all();
        return view('imports.create', compact('suppliers', 'products', 'accounts'), ['activeGroup' => 'inventory', 'activeName' => 'imports']);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // 1. Tạo phiếu nhập gốc
            $order = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'account_id' => $request->account_id,
                'total_product_value' => $request->total_product_value,
                'extra_cost' => $request->extra_cost ?? 0,
                'total_final_amount' => $request->total_product_value + ($request->extra_cost ?? 0),
                'paid_amount' => $request->paid_amount ?? 0,
            ]);

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                // 2. Phân bổ chi phí phát sinh (Extra Cost) vào giá nhập
                // Tỷ lệ = Giá trị dòng hàng / Tổng tiền hàng gốc
                $ratio = ($item['quantity'] * $item['import_price']) / $request->total_product_value;
                $allocated = (($ratio * $request->extra_cost) / $item['quantity']) ?? 0;
                $final_unit_cost = $item['import_price'] + $allocated;

                // 3. Tính Giá Vốn Bình Quân Gia Quyền (BQGQ)
                // (Tồn cũ * Giá vốn cũ + Nhập mới * Giá nhập thực tế) / Tổng tồn mới
                $old_total_value = $product->stock_quantity * $product->cost_price;
                $new_total_value = $item['quantity'] * $final_unit_cost;
                $new_qty = $product->stock_quantity + $item['quantity'];
                $new_cost_price = ($old_total_value + $new_total_value) / $new_qty;

                // 4. Lưu chi tiết phiếu nhập
                PurchaseDetail::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'import_price' => $item['import_price'],
                    'allocated_cost' => $allocated,
                    'final_unit_cost' => $final_unit_cost,
                ]);

                // 5. Cập nhật Sản phẩm & Thẻ kho
                $product->update([
                    'cost_price' => $new_cost_price,
                    'stock_quantity' => $new_qty,
                ]);

                StockLog::create([
                    'product_id' => $product->id,
                    'ref_type' => 'import',
                    'ref_id' => $order->id,
                    'change_qty' => $item['quantity'],
                    'final_qty' => $new_qty
                ]);
            }

            // 6. Xử lý Nợ NCC & Nhật ký nợ (Credit Log)
            $debt = $order->total_final_amount - $order->paid_amount;
            if ($debt > 0) {
                $supplier = Supplier::find($request->supplier_id);
                $old_debt = $supplier->total_debt;
                $supplier->increment('total_debt', $debt);

                CreditLog::create([
                    'target_type' => 'supplier',
                    'target_id' => $supplier->id,
                    'ref_type' => 'order',
                    'ref_id' => $order->id,
                    'change_amount' => $debt,
                    'new_balance' => $old_debt + $debt,
                    'note' => 'Nợ từ phiếu nhập hàng #' . $order->id
                ]);
            }

            // 7. Trừ tiền tài khoản nếu có thanh toán
            if ($order->paid_amount > 0) {
                Account::find($request->account_id)->decrement('current_balance', $order->paid_amount);
            }

            return redirect()->route('imports.index')->with('msg', 'Nhập hàng và tính lại giá vốn thành công!');
        });
    }

    //Hiển thị chi tiết phiếu nhập
    public function show($id)
    {
        // Load phiếu nhập kèm thông tin NCC, tài khoản chi và chi tiết từng sản phẩm
        $order = PurchaseOrder::with(['supplier', 'account', 'details.product'])->findOrFail($id);
        return view('imports.show', [
            'order' => $order,
            'activeGroup' => 'inventory',
            'activeName' => 'imports'
        ]);
    }
}
