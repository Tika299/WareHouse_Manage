<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReturnDetail extends Model
{
    //
    protected $fillable = ['customer_return_id', 'product_id', 'quantity', 'refund_price'];
}
