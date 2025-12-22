<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'activeGroup' => 'inventory', // Để menu KHO HÀNG sáng lên
            'activeName' => 'profile'    // Để nút Sản phẩm sáng lên
        ]);
    }
}
