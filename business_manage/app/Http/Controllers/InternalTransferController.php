<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\InternalTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternalTransferController extends Controller
{
    public function index() {
        return view('internal_transfers.index',[
            'activeGroup' => 'finance',
            'activeName' => 'accounts'
        ]);
    }

    public function create() {
        $accounts = Account::all();
        return view('internal_transfers.create', compact('accounts'), [
            'activeGroup' => 'finance', 'activeName' => 'accounts'
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'from_account_id' => 'required',
            'to_account_id' => 'required|different:from_account_id',
            'amount' => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $from = Account::lockForUpdate()->find($request->from_account_id);
            $to = Account::lockForUpdate()->find($request->to_account_id);

            if ($from->current_balance < $request->amount) {
                return back()->with('warning', 'Tài khoản nguồn không đủ số dư!');
            }

            // 1. Ghi nhận lệnh chuyển
            InternalTransfer::create($request->all());

            // 2. Trừ tiền tài khoản nguồn, cộng tiền tài khoản đích
            $from->decrement('current_balance', $request->amount);
            $to->increment('current_balance', $request->amount);

            return redirect()->route('accounts.index')->with('msg', 'Chuyển khoản nội bộ thành công!');
        });
    }
}
