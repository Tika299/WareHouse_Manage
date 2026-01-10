<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalExportDetail extends Model
{
    protected $fillable = ['internal_export_id', 'product_id', 'quantity', 'cost_price'];

    public function internalExport()
    {
        return $this->belongsTo(InternalExport::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
