<?php

// app/Http/Controllers/VoucherController.php
namespace App\Http\Controllers;

use App\Models\{CashVoucher, Account, Customer, Supplier, CreditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = CashVoucher::with(['account', 'customer', 'supplier'])->latest()->paginate(15);
        return view('vouchers.index', compact('vouchers'), [
            'activeGroup' => 'finance',
            'activeName' => 'vouchers'
        ]);
    }

    public function create(Request $request)
    {
        $accounts = Account::all();
        $customers = Customer::all();
        $suppliers = Supplier::all();

        // Nhận ID từ trang chi tiết nếu có
        $selected_customer = $request->customer_id;
        $selected_supplier = $request->supplier_id;

        return view('vouchers.create', compact('accounts', 'customers', 'suppliers', 'selected_customer', 'selected_supplier'), [
            'activeGroup' => 'finance',
            'activeName' => 'vouchers'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'voucher_type' => 'required',
            'category' => 'required',
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required',
        ]);

        return DB::transaction(function () use ($request) {
            $voucher = CashVoucher::create($request->all());
            $account = Account::lockForUpdate()->find($request->account_id);

            if ($request->voucher_type == 'receipt') {
                $account->increment('current_balance', $request->amount);
                // Xử lý nợ khách hàng (Tương tự như bên dưới nếu bạn muốn đơn bán cũng nhảy trạng thái)
                $this->allocateDebt($request->customer_id, $request->amount, 'customer');
            } else {
                $account->decrement('current_balance', $request->amount);

                // XỬ LÝ TRẢ NỢ NCC VÀ CẬP NHẬT PHIẾU NHẬP
                if ($request->category == 'debt_supplier' && $request->supplier_id) {
                    $this->allocateDebt($request->supplier_id, $request->amount, 'supplier');
                }
            }

            return redirect()->route('vouchers.index')->with('msg', 'Đã lưu phiếu và cập nhật trạng thái nợ!');
        });
    }

    /**
     * Hàm tự động phân bổ tiền trả nợ vào các đơn hàng cũ nhất
     */
    private function allocateDebt($targetId, $amount, $type)
    {
        $remainingAmount = $amount;

        if ($type == 'supplier') {
            // 1. Tìm các phiếu nhập còn nợ (paid < total), ưu tiên phiếu cũ nhất (id asc)
            $orders = \App\Models\PurchaseOrder::where('supplier_id', $targetId)
                ->whereRaw('paid_amount < total_final_amount')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($orders as $order) {
                if ($remainingAmount <= 0) break;

                $debtOfOrder = $order->total_final_amount - $order->paid_amount;

                if ($remainingAmount >= $debtOfOrder) {
                    // Trả hết nợ cho phiếu này
                    $order->increment('paid_amount', $debtOfOrder);
                    $remainingAmount -= $debtOfOrder;
                } else {
                    // Trả được một phần
                    $order->increment('paid_amount', $remainingAmount);
                    $remainingAmount = 0;
                }
            }

            // 2. Cập nhật tổng nợ gộp của NCC
            $supplier = \App\Models\Supplier::find($targetId);
            $oldDebt = $supplier->total_debt;
            $supplier->decrement('total_debt', $amount);

            // 3. Ghi Credit Log
            \App\Models\CreditLog::create([
                'target_type' => 'supplier',
                'target_id' => $targetId,
                'ref_type' => 'voucher',
                'ref_id' => 0, // Hoặc ID voucher
                'change_amount' => -$amount,
                'new_balance' => $oldDebt - $amount,
                'note' => 'Chi trả nợ gộp cho các phiếu nhập'
            ]);
        }

        // Tương tự cho Customer nếu bạn muốn trang Bán hàng cũng tự nhảy trạng thái
        if ($type == 'customer') {
            $orders = \App\Models\SalesOrder::where('customer_id', $targetId)
                ->whereRaw('paid_amount < total_final_amount')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($orders as $order) {
                if ($remainingAmount <= 0) break;
                $debtOfOrder = $order->total_final_amount - $order->paid_amount;
                if ($remainingAmount >= $debtOfOrder) {
                    $order->increment('paid_amount', $debtOfOrder);
                    $remainingAmount -= $debtOfOrder;
                } else {
                    $order->increment('paid_amount', $remainingAmount);
                    $remainingAmount = 0;
                }
            }
            $customer = \App\Models\Customer::find($targetId);
            $oldDebt = $customer->total_debt;
            $customer->decrement('total_debt', $amount);
            \App\Models\CreditLog::create([
                'target_type' => 'customer',
                'target_id' => $targetId,
                'ref_type' => 'voucher',
                'ref_id' => 0,
                'change_amount' => -$amount,
                'new_balance' => $oldDebt - $amount,
                'note' => 'Thu nợ gộp từ khách'
            ]);
        }
    }
}
