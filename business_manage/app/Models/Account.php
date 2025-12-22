<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'type', 'initial_balance', 'current_balance'];

    // Lấy các phiếu thu chi liên quan đến tài khoản này
    public function vouchers()
    {
        return $this->hasMany(CashVoucher::class);
    }
}
