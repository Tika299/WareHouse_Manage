<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private $currentParent = null;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $sku = trim($row['ma_sku'] ?? '');
            $productName = trim($row['ten_san_pham'] ?? '');
            
            // Bỏ qua dòng rác hoàn toàn
            if (empty($sku) && empty($productName)) continue;

            // --- BƯỚC 1: XỬ LÝ SẢN PHẨM CHA (PARENT) ---
            // Nếu dòng này có Tên sản phẩm -> Đây là khởi đầu của một nhóm sản phẩm
            if (!empty($productName)) {
                $this->currentParent = Product::updateOrCreate(
                    [
                        'name' => $productName,
                        'parent_id' => null, 
                    ],
                    [
                        'sku' => 'P-' . ($sku ?: Str::random(8)),
                        'product_type' => $row['loai_san_pham'] ?? 'Mỹ phẩm',
                        'brand' => $row['nhan_hieu'] ?? null,
                        'description' => $row['mo_ta_san_pham'] ?? null,
                        'unit' => $row['don_vi'] ?? 'Cái',
                        'stock_quantity' => 0, // Cha chỉ dùng để gom nhóm
                        'cost_price' => 0,
                    ]
                );
            }

            // --- BƯỚC 2: XỬ LÝ THUỘC TÍNH BIẾN THỂ (JSON) ---
            $variantAttributes = [];
            if (!empty($row['thuoc_tinh_1']) && !empty($row['gia_tri_thuoc_tinh_1'])) {
                $variantAttributes[$row['thuoc_tinh_1']] = $row['gia_tri_thuoc_tinh_1'];
            }
            if (!empty($row['thuoc_tinh_2']) && !empty($row['gia_tri_thuoc_tinh_2'])) {
                $variantAttributes[$row['thuoc_tinh_2']] = $row['gia_tri_thuoc_tinh_2'];
            }

            // --- BƯỚC 3: XỬ LÝ GIÁ & TỒN KHO ---
            // parseNumber giúp xử lý dấu chấm phân cách (1.250.000 -> 1250000)
            $costPrice = $this->parseNumber($row['pl_gia_nhap'] ?? 0);
            $mRetail    = $this->parseNumber($row['pl_gia_ban_le'] ?? 0);
            $mWholesale = $this->parseNumber($row['pl_gia_ban_buon'] ?? 0);
            $mCtv       = $this->parseNumber($row['pl_gia_ctv'] ?? 0);
            $mEcommerce = $this->parseNumber($row['pl_gia_san'] ?? 0);

            // --- BƯỚC 4: LƯU BIẾN THỂ CON ---
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'parent_id'     => $this->currentParent ? $this->currentParent->id : null,
                    'name'          => $this->currentParent->name,
                    'variant_label' => $row['ten_phien_ban_san_pham'] ?? ($row['gia_tri_thuoc_tinh_1'] ?? 'Mặc định'),
                    'attributes'    => count($variantAttributes) > 0 ? $variantAttributes : ['Kích thước' => 'Mặc định'],
                    'product_type'  => $this->currentParent->product_type,
                    'brand'         => $this->currentParent->brand,
                    'unit'          => $row['don_vi'] ?? $this->currentParent->unit,
                    'description'   => null, // Con không cần lưu lại mô tả dài của cha cho nhẹ DB
                    'cost_price'    => $costPrice,
                    'stock_quantity' => intval($row['lc_cn1_ton_kho_ban_dau'] ?? 0),
                    'min_stock'     => intval($row['lc_cn1_ton_toi_thieu'] ?? 5),

                    // Lưu giá thủ công, nếu Excel là 0 thì lưu NULL để dùng công thức hệ số
                    'manual_retail_price'    => $mRetail > 0 ? $mRetail : null,
                    'manual_wholesale_price' => $mWholesale > 0 ? $mWholesale : null,
                    'manual_ctv_price'       => $mCtv > 0 ? $mCtv : null,
                    'manual_ecommerce_price' => $mEcommerce > 0 ? $mEcommerce : null,
                ]
            );
        }
    }

    private function parseNumber($value) {
        if (is_null($value) || $value === '') return 0;
        if (is_numeric($value)) return (float)$value;
        // Xử lý chuỗi "1.250.000" -> "1250000"
        $clean = str_replace(['.', ','], ['', '.'], $value);
        return is_numeric($clean) ? (float)$clean : 0;
    }
}