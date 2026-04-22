<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    //
    protected $fillable = ['combo_id', 'product_id', 'quantity'];

    public function component()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
