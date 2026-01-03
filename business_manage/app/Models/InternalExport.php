<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalExport extends Model
{
    protected $fillable = ['user_id', 'reason_type', 'note', 'total_cost_value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(InternalExportDetail::class);
    }
}
