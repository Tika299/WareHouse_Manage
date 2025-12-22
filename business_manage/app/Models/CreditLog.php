<?php

// app/Models/CreditLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    protected $fillable = [
        'target_type', // customer / supplier
        'target_id',
        'ref_type',    // order / voucher / barter
        'ref_id',
        'change_amount',
        'new_balance',
        'note'
    ];

    /**
     * Lấy thông tin đối tượng (Khách hàng hoặc NCC)
     */
    public function target()
    {
        if ($this->target_type === 'customer') {
            return $this->belongsTo(Customer::class, 'target_id');
        }
        return $this->belongsTo(Supplier::class, 'target_id');
    }
}
