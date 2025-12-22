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
            // 1. Tạo phiếu
            $voucher = CashVoucher::create($request->all());

            // 2. Cập nhật số dư Tài khoản (Accounts)
            $account = Account::lockForUpdate()->find($request->account_id);
            if ($request->voucher_type == 'receipt') {
                $account->increment('current_balance', $request->amount);
            } else {
                $account->decrement('current_balance', $request->amount);
            }

            // 3. Xử lý THU NỢ KHÁCH HÀNG (debt_customer)
            if ($request->category == 'debt_customer' && $request->customer_id) {
                $customer = Customer::lockForUpdate()->find($request->customer_id);
                $oldDebt = $customer->total_debt;
                $customer->decrement('total_debt', $request->amount);

                CreditLog::create([
                    'target_type' => 'customer',
                    'target_id' => $customer->id,
                    'ref_type' => 'voucher',
                    'ref_id' => $voucher->id,
                    'change_amount' => -$request->amount, // Trả nợ là biến động âm
                    'new_balance' => $oldDebt - $request->amount,
                    'note' => 'Thu nợ gộp: ' . $request->note
                ]);
            }

            // 4. Xử lý TRẢ NỢ NCC (debt_supplier)
            if ($request->category == 'debt_supplier' && $request->supplier_id) {
                $supplier = Supplier::lockForUpdate()->find($request->supplier_id);
                $oldDebt = $supplier->total_debt;
                $supplier->decrement('total_debt', $request->amount);

                CreditLog::create([
                    'target_type' => 'supplier',
                    'target_id' => $supplier->id,
                    'ref_type' => 'voucher',
                    'ref_id' => $voucher->id,
                    'change_amount' => -$request->amount,
                    'new_balance' => $oldDebt - $request->amount,
                    'note' => 'Trả nợ NCC: ' . $request->note
                ]);
            }

            return redirect()->route('vouchers.index')->with('msg', 'Đã lưu phiếu và cập nhật sổ quỹ thành công!');
        });
    }
}
