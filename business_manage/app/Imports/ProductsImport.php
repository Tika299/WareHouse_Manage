<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Sử dụng hàng đầu làm tiêu đề

class ProductImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Logic: Nếu SKU đã tồn tại thì cập nhật, nếu chưa thì tạo mới
        return Product::updateOrCreate(
            ['sku' => $row['ma_sku']], // Cột trong Excel phải đặt tên là ma_sku
            [
                'name'              => $row['ten_san_pham'],
                'unit'              => $row['don_vi_tinh'] ?? 'Cái',
                'cost_price'        => $row['gia_von'] ?? 0,
                'markup_retail'     => $row['muc_cong_le'] ?? 0,
                'markup_wholesale'  => $row['muc_cong_si'] ?? 0,
                'stock_quantity'    => $row['ton_kho'] ?? 0,
                'min_stock'         => $row['ton_toi_thieu'] ?? 5,
            ]
        );
    }
}

