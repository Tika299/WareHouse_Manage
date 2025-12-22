<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAudit extends Model
{
    // Thêm dòng này để cho phép lưu dữ liệu vào các cột tương ứng
    protected $fillable = ['user_id', 'note', 'total_diff_value'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockAuditDetail::class);
    }
}
