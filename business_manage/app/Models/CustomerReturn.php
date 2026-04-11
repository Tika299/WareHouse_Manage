<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReturn extends Model
{
    //
    protected $fillable = ['customer_id', 'sales_order_id', 'user_id', 'total_return_value', 'note'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
