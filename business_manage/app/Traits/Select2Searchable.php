<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Select2Searchable
{
    public function performSelect2Search(Request $request, $model, $searchColumns = ['name'])
    {
        $search = $request->get('q');
        $page = $request->get('page', 1);
        $perPage = 15; // Tăng lên 15 để thanh cuộn hiện rõ hơn
        $offset = ($page - 1) * $perPage;

        $query = $model::query();

        if ($search) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%$search%");
                }
            });
        }

        $totalCount = (clone $query)->count();

        $items = $query->limit($perPage)->offset($offset)->get()->map(function ($item) {
            // Cấu trúc mặc định cho Select2
            $response = [
                'id'   => $item->id,
                'text' => $item->name,
            ];

            // Nếu là Sản phẩm (có SKU) thì thêm dữ liệu để add vào table
            if (isset($item->sku)) {
                $response['text']       = "[" . $item->sku . "] " . $item->name . " (Tồn: " . $item->stock_quantity . ")";
                $response['sku']        = $item->sku;
                $response['name']       = $item->name;
                $response['stock']      = $item->stock_quantity;
                $response['cost_price'] = $item->cost_price;
                $response['retail_price'] = $item->retail_price;
            }
            // Nếu là Nhà cung cấp (có phone) thì hiện thêm SĐT cho dễ tìm
            elseif (isset($item->phone)) {
                $response['text'] = $item->name . " - " . $item->phone;
            }

            return $response;
        });

        return response()->json([
            'results' => $items,
            'pagination' => ['more' => ($offset + $perPage) < $totalCount]
        ]);
    }
}
