<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashVoucher;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        $totalBalance = Account::sum('current_balance');
        return view('accounts.index', compact('accounts', 'totalBalance'), [
            'activeGroup' => 'finance',
            'activeName' => 'accounts'
        ]);
    }

    public function create()
    {
        return view('accounts.create', ['activeGroup' => 'finance', 'activeName' => 'accounts']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:accounts,name|max:255',
            'type' => 'required|in:cash,bank',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        // SPEC: current_balance sẽ bằng initial_balance tại thời điểm khởi tạo
        $account = \App\Models\Account::create([
            'name' => $request->name,
            'type' => $request->type,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
        ]);

        // Nếu là yêu cầu AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Thêm tài khoản thành công!'
            ]);
        }

        return redirect()->route('accounts.index')->with('msg', 'Đã khởi tạo tài khoản thành công!');
    }

    /**
     * TRANG SỔ CHI TIẾT TÀI KHOẢN (Ledger)
     */
    public function show(Account $account)
    {
        $vouchers = CashVoucher::where('account_id', $account->id)
            ->latest()
            ->paginate(20);

        return view('accounts.show', compact('account', 'vouchers'), [
            'activeGroup' => 'finance',
            'activeName' => 'accounts'
        ]);
    }

    public function edit(Account $account)
    {
        return view('accounts.edit', [
            'account' => $account,
            'activeGroup' => 'finance',
            'activeName' => 'accounts'
        ]);
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'type' => 'required|in:cash,bank',
            'initial_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric|min:0',
        ]);

        $account->update([
            'name' => $request->name,
            'type' => $request->type,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->current_balance,
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('msg', 'Đã cập nhật số dư tài khoản thành công!');
    }
}
