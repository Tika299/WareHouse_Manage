<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventoryLookup.index', [
            'activeGroup' => 'inventory', // Để menu KHO HÀNG sáng lên
            'activeName' => 'stock'    // Để nút Sản phẩm sáng lên
        ]);
    }
}
