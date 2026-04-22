<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait Select2Searchable
{
    public function performSelect2Search(Request $request, $model, $searchColumns = ['name'], $extraQuery = null)
    {
        $search = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $query = $model::query();

        // Nếu có các điều kiện lọc thêm (ví dụ lọc bỏ sản phẩm Cha P-)
        if ($extraQuery && is_callable($extraQuery)) {
            $extraQuery($query);
        }

        if ($search) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
                // Nếu là model Product, tìm thêm trong variant_label
                if (method_exists($q->getModel(), 'getTable') && $q->getModel()->getTable() === 'products') {
                    $q->orWhere('variant_label', 'LIKE', "%$search%");
                }
            });
        }

        $totalCount = (clone $query)->count();
        $items = $query->limit($perPage)->offset($offset)->get();

        $results = $items->map(function ($item) {
            // TỰ ĐỘNG NHẬN DIỆN DỮ LIỆU TRẢ VỀ THEO MODEL
            if (isset($item->phone)) {
                // Định dạng cho Khách hàng
                return [
                    'id'   => $item->id,
                    'text' => $item->name . " - " . $item->phone . ($item->address ? " (" . $item->address . ")" : ""),
                    'name' => $item->name,
                    'phone' => $item->phone
                ];
            } else {
                // Định dạng cho Sản phẩm
                return [
                    'id'            => $item->id,
                    'text'          => "[" . $item->sku . "] " . $item->name . ($item->variant_label ? " - " . $item->variant_label : "") . " (Tồn: " . $item->stock_quantity . ")",
                    'sku'           => $item->sku,
                    'name'          => $item->name,
                    'variant_label' => $item->variant_label,
                    'stock'         => $item->stock_quantity,
                    'retail_price'  => $item->retail_price,
                    'cost_price'    => $item->cost_price,
                ];
            }
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => ($offset + $perPage) < $totalCount]
        ]);
    }
}
