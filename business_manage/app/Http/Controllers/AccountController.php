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
        \App\Models\Account::create([
            'name' => $request->name,
            'type' => $request->type,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
        ]);

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
}
