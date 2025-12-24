<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
            'sku'               => $row['ma_sku'] ?? $row['sku'] ?? null,
            'name'              => $row['ten_san_pham'] ?? $row['name'] ?? null,
            'unit'              => $row['don_vi_tinh'] ?? $row['unit'] ?? 'cái',
            'cost_price'        => $row['gia_von'] ?? $row['cost_price'] ?? 0,
            'markup_retail'     => $row['muc_cong_le'] ?? $row['markup_retail'] ?? 0,
            'markup_wholesale'  => $row['muc_cong_si'] ?? $row['markup_wholesale'] ?? 0,
            'stock_quantity'    => $row['ton_kho'] ?? $row['stock_quantity'] ?? 0,
            'min_stock'         => $row['ton_toi_thieu'] ?? $row['min_stock'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'ma_sku' => 'required|unique:products,sku',
            'ten_san_pham' => 'required',
            'don_vi_tinh' => 'nullable',
            'gia_von' => 'required|numeric|min:0',
            'muc_cong_le' => 'required|numeric|min:0',
            'muc_cong_si' => 'required|numeric|min:0',
            'ton_kho' => 'required|integer|min:0',
            'ton_toi_thieu' => 'required|integer|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'ma_sku.required' => 'Mã SKU là bắt buộc',
            'ma_sku.unique' => 'Mã SKU đã tồn tại trong hệ thống',
            'ten_san_pham.required' => 'Tên sản phẩm là bắt buộc',
            'gia_von.required' => 'Giá vốn là bắt buộc',
            'gia_von.numeric' => 'Giá vốn phải là số',
            'muc_cong_le.required' => 'Mức cộng lẻ là bắt buộc',
            'muc_cong_si.required' => 'Mức cộng sỉ là bắt buộc',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}