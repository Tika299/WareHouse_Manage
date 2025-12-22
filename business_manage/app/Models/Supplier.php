<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'total_debt'];

    /**
     * Lấy các phiếu nhập hàng từ NCC này
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Lấy lịch sử biến động nợ (Credit Log) của NCC này
     */
    public function creditLogs()
    {
        return $this->hasMany(CreditLog::class, 'target_id')
            ->where('target_type', 'supplier')
            ->latest();
    }
}
