<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private $currentParentName = null;

    public function collection(Collection $rows)
    {
        $groupedProducts = [];

        // BƯỚC 1: Gom nhóm sản phẩm (Xử lý dòng trống tên sản phẩm bên dưới)
        foreach ($rows as $row) {
            $name = trim($row['ten_san_pham'] ?? '');
            if (!empty($name)) {
                $this->currentParentName = $name;
            }
            if ($this->currentParentName) {
                $groupedProducts[$this->currentParentName][] = $row;
            }
        }

        // BƯỚC 2: Xử lý từng nhóm sản phẩm
        foreach ($groupedProducts as $productName => $items) {
            $firstItem = $items[0]; // Dòng đầu tiên của nhóm thường chứa mô tả, ngành hàng, nhãn hiệu
            $attrValue1 = trim($firstItem['gia_tri_thuoc_tinh_1'] ?? '');

            // Xử lý Metadata (Lấy ID hoặc tạo mới)
            $categoryId = $this->getMetadataId(Category::class, $firstItem['loai_san_pham'] ?? 'Chưa phân loại');
            $brandId = $this->getMetadataId(Brand::class, $firstItem['nhan_hieu'] ?? 'Không có thương hiệu');

            // Lấy mô tả từ dòng đầu tiên của nhóm (Tránh việc các dòng sau bị trống mô tả)
            $description = $firstItem['mo_ta_san_pham'] ?? $firstItem['mo_ta'] ?? null;

            // TRƯỜNG HỢP 1: Sản phẩm ĐƠN LẺ (Giá trị thuộc tính 1 là "Mặc định")
            if ($attrValue1 === 'Mặc định' || empty($attrValue1)) {
                foreach ($items as $item) {
                    // Truyền thêm biến $description vào hàm save
                    $this->saveProduct($item, null, 'single', $productName, $categoryId, $brandId, $description);
                }
            } 
            // TRƯỜNG HỢP 2: Sản phẩm CÓ BIẾN THỂ
            else {
                // 1. Tạo bản ghi CHA (Lưu mô tả vào đây)
                $parent = Product::updateOrCreate(
                    ['name' => $productName, 'parent_id' => null],
                    [
                        'sku' => 'P-' . (trim($firstItem['ma_sku']) ?: Str::random(8)),
                        'category_id' => $categoryId,
                        'brand_id' => $brandId,
                        'description' => $description, // Quan trọng: Lưu mô tả cho hàng CHA
                        'unit' => $firstItem['don_vi'] ?? 'Cái',
                        'stock_quantity' => 0,
                        'cost_price' => 0,
                    ]
                );

                // 2. Tạo các bản ghi CON (Không cần lưu lại mô tả để nhẹ DB)
                foreach ($items as $item) {
                    $this->saveProduct($item, $parent->id, 'variant', $productName, $categoryId, $brandId, null);
                }
            }
        }
    }

    /**
     * Hàm lưu sản phẩm (Xử lý 3 thuộc tính và mô tả)
     */
    private function saveProduct($row, $parentId, $type, $productName, $categoryId, $brandId, $description)
    {
        $sku = trim($row['ma_sku'] ?? '');
        if (empty($sku)) return null;

        // Xử lý gộp 3 thuộc tính cho tên phiên bản
        $labels = [];
        if (!empty($row['gia_tri_thuoc_tinh_1']) && trim($row['gia_tri_thuoc_tinh_1']) !== 'Mặc định') 
            $labels[] = trim($row['gia_tri_thuoc_tinh_1']);
        if (!empty($row['gia_tri_thuoc_tinh_2'])) $labels[] = trim($row['gia_tri_thuoc_tinh_2']);
        if (!empty($row['gia_tri_thuoc_tinh_3'])) $labels[] = trim($row['gia_tri_thuoc_tinh_3']);
        
        $variantLabel = implode(' - ', $labels);

        // Ưu tiên tên phiên bản có sẵn trong Excel nếu có
        if (!empty($row['ten_phien_ban_san_pham'])) {
            $variantLabel = trim($row['ten_phien_ban_san_pham']);
        }

        return Product::updateOrCreate(
            ['sku' => $sku],
            [
                'parent_id'     => $parentId,
                'name'          => $productName,
                'category_id'   => $categoryId,
                'brand_id'      => $brandId,
                'variant_label' => ($type === 'single') ? null : $variantLabel,
                'description'   => $description, // Lưu mô tả (Nếu là single thì có, nếu là variant con thì null)
                'unit'          => $row['don_vi'] ?? 'Cái',
                'cost_price'    => $this->parseMoney($row['pl_gia_nhap'] ?? 0),
                'stock_quantity'=> max(0, intval($row['lc_cn1_ton_kho_ban_dau'] ?? 0)),
                'min_stock'     => intval($row['lc_cn1_ton_toi_thieu'] ?? 5),

                // Giá bán
                'manual_retail_price'    => $this->parseMoney($row['pl_gia_ban_le'] ?? 0),
                'manual_wholesale_price' => $this->parseMoney($row['pl_gia_ban_buon'] ?? 0),
                'manual_ctv_price'       => $this->parseMoney($row['pl_gia_ctv'] ?? 0),
                'manual_ecommerce_price' => $this->parseMoney($row['pl_gia_san'] ?? 0),

                // Hệ số mặc định
                'factor_retail'    => 1.5,
                'factor_wholesale' => 1.1,
                'factor_ctv'       => 1.2,
                'factor_eco_margin' => 0.5,
                'factor_eco_fee'   => 0.3,
            ]
        );
    }

    private function getMetadataId($model, $name)
    {
        $name = trim($name);
        if (empty($name)) return null;
        $record = $model::firstOrCreate(['name' => $name]);
        return $record->id;
    }

    private function parseMoney($value) {
        if (is_null($value) || $value === '') return 0;
        if (is_numeric($value)) return (float)$value;
        $clean = str_replace(['.', ','], '', $value);
        return is_numeric($clean) ? (float)$clean : 0;
    }
}