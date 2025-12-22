<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouseTransfer.index', [
            'activeGroup' => 'inventory', // Để menu KHO HÀNG sáng lên
            'activeName' => 'warehouse'    // Để nút Sản phẩm sáng lên
        ]);
    }
}
