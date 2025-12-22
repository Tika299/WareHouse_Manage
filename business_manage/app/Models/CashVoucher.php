<?php

// app/Models/CashVoucher.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashVoucher extends Model
{
    protected $fillable = [
        'voucher_type', // receipt (thu), payment (chi)
        'category',     // debt_customer, debt_supplier, operational, other
        'account_id',   // Tài khoản tiền mặt/ngân hàng
        'customer_id', 
        'supplier_id', 
        'amount', 
        'note'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
}