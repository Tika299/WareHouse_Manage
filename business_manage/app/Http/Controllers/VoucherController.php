<?php

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
            'activeName' => 'vouchers',
        ]);
    }

    public function create(Request $request)
    {
        $accounts = Account::all();
        $customers = Customer::all();
        $suppliers = Supplier::all();

        $selected_customer = $request->customer_id;
        $selected_supplier = $request->supplier_id;

        return view('vouchers.create', compact(
            'accounts',
            'customers',
            'suppliers',
            'selected_customer',
            'selected_supplier'
        ), [
            'activeGroup' => 'finance',
            'activeName' => 'vouchers',
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
                $this->allocateDebt($request->customer_id, $request->amount, 'customer');
            } else {
                $account->decrement('current_balance', $request->amount);

                if ($request->category == 'debt_supplier' && $request->supplier_id) {
                    $this->allocateDebt($request->supplier_id, $request->amount, 'supplier');
                }
            }

            return redirect()
                ->route('vouchers.index')
                ->with('msg', 'Đã lưu phiếu và cập nhật trạng thái nợ!');
        });
    }

    /**
     * Auto allocate payment into oldest unpaid orders.
     */
    private function allocateDebt($targetId, $amount, $type)
    {
        if ($amount <= 0) {
            throw new \Exception('Số tiền phân bổ phải lớn hơn 0.');
        }

        $remainingAmount = $amount;
        $allocatedAmount = 0;

        if ($type === 'supplier') {
            $supplier = \App\Models\Supplier::lockForUpdate()->findOrFail($targetId);

            if ($amount > $supplier->total_debt) {
                throw new \Exception('Số tiền trả vượt quá tổng công nợ hiện tại của nhà cung cấp.');
            }

            $orders = \App\Models\PurchaseOrder::where('supplier_id', $targetId)
                ->whereRaw('paid_amount < total_final_amount')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $debtOfOrder = $order->total_final_amount - $order->paid_amount;
                $payAmount = min($remainingAmount, $debtOfOrder);

                $order->increment('paid_amount', $payAmount);
                $remainingAmount -= $payAmount;
                $allocatedAmount += $payAmount;
            }

            if ($remainingAmount > 0) {
                throw new \Exception('Không thể phân bổ hết số tiền trả vào các phiếu nhập còn nợ.');
            }

            $oldDebt = $supplier->total_debt;
            $supplier->decrement('total_debt', $allocatedAmount);

            CreditLog::create([
                'target_type' => 'supplier',
                'target_id' => $targetId,
                'ref_type' => 'voucher',
                'ref_id' => 0,
                'change_amount' => -$allocatedAmount,
                'new_balance' => $oldDebt - $allocatedAmount,
                'note' => 'Chi trả nợ gộp cho các phiếu nhập',
            ]);

            return $allocatedAmount;
        }

        if ($type === 'customer') {
            $customer = \App\Models\Customer::lockForUpdate()->findOrFail($targetId);

            if ($amount > $customer->total_debt) {
                throw new \Exception('Số tiền trả vượt quá tổng công nợ hiện tại của khách hàng.');
            }

            $orders = \App\Models\SalesOrder::where('customer_id', $targetId)
                ->whereRaw('paid_amount < total_final_amount')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($orders as $order) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $debtOfOrder = $order->total_final_amount - $order->paid_amount;
                $payAmount = min($remainingAmount, $debtOfOrder);

                $order->increment('paid_amount', $payAmount);
                $remainingAmount -= $payAmount;
                $allocatedAmount += $payAmount;
            }

            if ($remainingAmount > 0) {
                throw new \Exception('Không thể phân bổ hết số tiền trả vào các đơn bán còn nợ.');
            }

            $oldDebt = $customer->total_debt;
            $customer->decrement('total_debt', $allocatedAmount);

            CreditLog::create([
                'target_type' => 'customer',
                'target_id' => $targetId,
                'ref_type' => 'voucher',
                'ref_id' => 0,
                'change_amount' => -$allocatedAmount,
                'new_balance' => $oldDebt - $allocatedAmount,
                'note' => 'Thu nợ gộp từ khách',
            ]);

            return $allocatedAmount;
        }

        throw new \Exception('Loại đối tượng phân bổ công nợ không hợp lệ.');
    }
}
