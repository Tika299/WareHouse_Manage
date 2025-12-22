<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'total_debt'];

    public function creditLogs()
    {
        return $this->hasMany(CreditLog::class, 'target_id')->where('target_type', 'customer');
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
