<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingUnit extends Model
{
    protected $fillable = ['name', 'phone', 'address'];

    /**
     * Mối quan hệ: Một đơn vị vận chuyển có nhiều đơn hàng
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    } //
}
