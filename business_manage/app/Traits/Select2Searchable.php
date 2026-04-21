<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait Select2Searchable
{
    public function performSelect2Search(Request $request, $model, $searchColumns = ['name', 'sku'])
    {
        $search = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $query = $model::query();

        // LOGIC MỚI: 
        // 1. Chỉ lấy những sản phẩm THỰC (Đơn lẻ hoặc Biến thể con)
        // 2. Loại bỏ sản phẩm Cha (thường có SKU bắt đầu bằng P- hoặc có parent_id = null nhưng có con)
        // Cách an toàn nhất: Loại bỏ những sản phẩm có SKU chứa chữ "P-" ở đầu
        $query->where('sku', 'NOT LIKE', 'P-%');

        if ($search) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
                // Tìm thêm trong nhãn biến thể (ví dụ: 100ml, 500ml)
                $q->orWhere('variant_label', 'LIKE', "%$search%");
            });
        }

        // Clone query để đếm tổng số bản ghi (tránh lỗi Infinity Scroll)
        $totalCount = (clone $query)->count();

        $items = $query->limit($perPage)->offset($offset)->get()->map(function($item) {
            return [
                'id'    => $item->id,
                // Hiển thị tên đầy đủ để nhân viên chọn không nhầm
                'text'  => "[" . $item->sku . "] " . $item->name . ($item->variant_label ? " - " . $item->variant_label : "") . " (Tồn: " . $item->stock_quantity . ")",
                'sku'   => $item->sku,
                'name'  => $item->name,
                'variant_label' => $item->variant_label,
                'stock' => $item->stock_quantity,
                'retail_price'  => $item->retail_price, // Ăn theo Accessor tính giá của bạn
                'cost_price'    => $item->cost_price,
                'description'   => $item->description,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => ($offset + $perPage) < $totalCount
            ]
        ]);
    }
}