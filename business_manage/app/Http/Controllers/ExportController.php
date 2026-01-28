<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CreditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\PurchaseOrder;
use App\Models\SalesDetail;
use App\Models\SalesOrder;
use App\Models\ShippingUnit;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query()->with('customer');
        // 1. Tìm kiếm theo mã đơn hàng
        if ($request->filled('order_id')) {
            $query->where('id', $request->order_id);
        }

        // 2. Lọc theo khách hàng
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 3. Lọc theo đơn vị vận chuyển
        if ($request->filled('shipping_unit_id')) {
            $query->where('shipping_unit_id', $request->shipping_unit_id);
        }

        // 4. Lọc theo khoảng ngày tháng
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

        // Lấy danh sách khách hàng để đổ vào dropdown lọc
        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            // Tìm khách hàng dựa trên ID từ link URL
            $selectedCustomer = Customer::find($request->customer_id);
        }

        return view('exports.index', [
            'orders' => $orders,
            'selectedCustomer' => $selectedCustomer,
            'activeGroup' => 'sales', // Để menu KHO HÀNG sáng lên
            'activeName' => 'orders'    // Để nút Sản phẩm sáng lên
        ]);
    }

    public function create()
    {
        // 1. Lấy toàn bộ danh sách cần thiết cho Form
        $products = Product::where('stock_quantity', '>', 0)->get(); // Chỉ lấy hàng còn tồn kho
        $customers = Customer::all();
        $shippingUnits = ShippingUnit::all();
        $accounts = Account::all();

        // 2. Truyền tất cả biến sang View
        return view('exports.create', [
            'products' => $products,
            'customers' => $customers,
            'shippingUnits' => $shippingUnits,
            'accounts' => $accounts,
            'activeGroup' => 'sales',
            'activeName' => 'orders'
        ]);
    }

    public function show($id)
    {
        // Load đơn hàng cùng với các quan hệ: chi tiết đơn, sản phẩm, khách hàng và ĐV vận chuyển
        $order = SalesOrder::with(['details.product', 'customer', 'shippingUnit'])->findOrFail($id);

        return view('exports.show', [
            'order' => $order,
            'activeGroup' => 'sales',
            'activeName' => 'orders'
        ]);
    }

    public function store(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'customer_id' => 'required',
            'account_id' => 'required',
            'items' => 'required|array',
            'shipping_unit_id' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Tính toán tiền hàng và phí vận chuyển
            $totalProductAmount = 0;
            foreach ($request->items as $item) {
                $totalProductAmount += $item['quantity'] * $item['unit_price'];
            }

            $shipFee = $request->shipping_fee ?? 0;
            // SPEC: Nếu khách chịu phí, cộng vào đơn. Nếu shop chịu, đơn chỉ tính tiền hàng.
            $totalFinal = $totalProductAmount + ($request->shipping_payor == 'customer' ? $shipFee : 0);

            // 2. Tạo đơn hàng chính
            $order = SalesOrder::create([
                'customer_id' => $request->customer_id,
                'account_id' => $request->account_id,
                'shipping_unit_id' => $request->shipping_unit_id,
                'shipping_fee' => $shipFee,
                'shipping_payor' => $request->shipping_payor,
                'total_product_amount' => $totalProductAmount,
                'total_final_amount' => $totalFinal,
                'paid_amount' => $request->paid_amount ?? 0,
            ]);

            // 3. Xử lý từng sản phẩm
            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                // Kiểm tra tồn kho
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} không đủ tồn kho!");
                }

                // Lưu chi tiết & CHỐT GIÁ VỐN tại thời điểm bán (BQGQ) để tính lãi sau này
                SalesDetail::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price_at_sale' => $product->cost_price // Quan trọng: Giá vốn hiện tại
                ]);

                // Trừ tồn kho & Ghi thẻ kho
                $oldQty = $product->stock_quantity;
                $newQty = $oldQty - $item['quantity'];
                $product->update(['stock_quantity' => $newQty]);

                StockLog::create([
                    'product_id' => $product->id,
                    'ref_type' => 'export',
                    'ref_id' => $order->id,
                    'change_qty' => -$item['quantity'],
                    'final_qty' => $newQty
                ]);
            }

            // 4. Cập nhật Nợ gộp khách hàng
            $debt = $totalFinal - ($request->paid_amount ?? 0);
            $customer = Customer::find($request->customer_id);
            if ($debt > 0) {
                $oldCustomerDebt = $customer->total_debt;
                $customer->increment('total_debt', $debt);

                // Ghi Credit Log
                CreditLog::create([
                    'target_type' => 'customer',
                    'target_id' => $customer->id,
                    'ref_type' => 'order',
                    'ref_id' => $order->id,
                    'change_amount' => $debt,
                    'new_balance' => $oldCustomerDebt + $debt,
                    'note' => "Mua hàng đơn #DH{$order->id}" . ($request->shipping_payor == 'customer' ? ' (Gồm phí ship)' : '')
                ]);
            }

            // 5. Cập nhật số dư tài khoản tiền (Nếu khách có trả tiền mặt/chuyển khoản)
            if ($request->paid_amount > 0) {
                Account::find($request->account_id)->increment('current_balance', $request->paid_amount);
            }

            return redirect()->route('exports.index')->with('msg', 'Tạo đơn hàng thành công!');
        });
    }

    public function barter()
    {
        $customers = Customer::all();
        $products = Product::all();
        $accounts = Account::all();
        $shippingUnits = ShippingUnit::all(); // Lấy từ DB
        $suppliers = Supplier::all(); // Lấy từ DB

        return view('exports.barter', compact('customers', 'products', 'accounts', 'shippingUnits', 'suppliers'), [
            'activeGroup' => 'sales',
            'activeName' => 'returnforms'
        ]);
    }

    public function storeBarter(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'account_id' => 'required',
            'shipping_unit_id' => 'required', // Yêu cầu chọn từ Form
            'export_items' => 'required|array',
            'import_items' => 'required|array',
        ]);

        return DB::transaction(function () use ($request) {
            // --- 1. Tính toán giá trị ---
            $totalExport = collect($request->export_items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
            $totalImport = collect($request->import_items)->sum(fn($item) => $item['quantity'] * $item['buyback_price']);
            $difference = $totalExport - $totalImport;

            // --- 2. Tạo Đơn xuất hàng ---
            $salesOrder = SalesOrder::create([
                'customer_id' => $request->customer_id,
                'account_id' => $request->account_id,
                'shipping_unit_id' => $request->shipping_unit_id, // Lấy từ Form thay vì để số 1
                'total_product_amount' => $totalExport,
                'total_final_amount' => $totalExport,
                'paid_amount' => $request->paid_amount ?? 0,
                'order_type' => 'barter'
            ]);

            // Trừ kho hàng xuất (Giữ nguyên logic cũ nhưng đảm bảo dùng biến salesOrder)
            foreach ($request->export_items as $item) {
                $p = Product::lockForUpdate()->find($item['product_id']);
                SalesDetail::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $p->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price_at_sale' => $p->cost_price
                ]);
                $p->decrement('stock_quantity', $item['quantity']);
                StockLog::create([
                    'product_id' => $p->id,
                    'ref_type' => 'barter',
                    'ref_id' => $salesOrder->id,
                    'change_qty' => -$item['quantity'],
                    'final_qty' => $p->stock_quantity
                ]);
            }

            // --- 3. Xử lý hàng THU VỀ (Hạch toán Nhập kho) ---
            // Lấy Nhà cung cấp đầu tiên hoặc tạo một NCC tên "Khách hàng đổi trả" nếu chưa có
            $supplier = Supplier::firstOrCreate(
                ['name' => 'Hệ thống đổi trả'], // Tìm NCC tên này
                ['phone' => '0000', 'address' => 'Nội bộ'] // Nếu ko có thì tạo mới
            );

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $supplier->id, // Lấy ID động từ DB
                'account_id' => $request->account_id,
                'total_product_value' => $totalImport,
                'total_final_amount' => $totalImport,
                'paid_amount' => 0,
            ]);

            foreach ($request->import_items as $item) {
                $p = Product::lockForUpdate()->find($item['product_id']);

                // Tính lại giá vốn BQGQ cho hàng thu về
                $oldValue = $p->stock_quantity * $p->cost_price;
                $newValue = $item['quantity'] * $item['buyback_price'];
                $newQty = $p->stock_quantity + $item['quantity'];
                $newCost = $newQty > 0 ? ($oldValue + $newValue) / $newQty : $item['buyback_price'];

                $p->update(['cost_price' => $newCost, 'stock_quantity' => $newQty]);

                PurchaseDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $p->id,
                    'quantity' => $item['quantity'],
                    'import_price' => $item['buyback_price'],
                    'allocated_cost' => 0,
                    'final_unit_cost' => $item['buyback_price']
                ]);

                StockLog::create([
                    'product_id' => $p->id,
                    'ref_type' => 'barter',
                    'ref_id' => $purchaseOrder->id,
                    'change_qty' => $item['quantity'],
                    'final_qty' => $newQty
                ]);
            }

            // --- 4. Cập nhật NỢ GỘP Khách hàng ---
            $finalChange = $difference - ($request->paid_amount ?? 0);
            $customer = Customer::find($request->customer_id);
            $oldDebt = $customer->total_debt;
            $customer->increment('total_debt', $finalChange);

            CreditLog::create([
                'target_type' => 'customer',
                'target_id' => $customer->id,
                'ref_type' => 'barter',
                'ref_id' => $salesOrder->id,
                'change_amount' => $finalChange,
                'new_balance' => $oldDebt + $finalChange,
                'note' => "Đổi hàng đơn #DH{$salesOrder->id}. Xuất: " . number_format($totalExport) . "đ, Thu: " . number_format($totalImport) . "đ"
            ]);

            return redirect()->route('returnforms.index')->with('msg', 'Giao dịch đổi hàng hoàn tất!');
        });
    }
}
