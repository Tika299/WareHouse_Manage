<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;

class ReturnFormController extends Controller
{
    public function index()
    {
        // 1. Lấy danh sách các đơn hàng có loại là 'barter' (đổi hàng)
        // with('customer') để load tên khách hàng nhanh hơn
        $returns = SalesOrder::where('order_type', 'barter')
            ->with(['customer'])
            ->latest()
            ->paginate(15);

        // 2. Truyền biến sang View (Lưu ý tên biến phải khớp với tên trong Blade)
        return view('return_forms.index', [
            'returns' => $returns, // ĐÂY LÀ DÒNG QUAN TRỌNG NHẤT
            'activeGroup' => 'sales',
            'activeName' => 'returnforms'
        ]);
    }
}
