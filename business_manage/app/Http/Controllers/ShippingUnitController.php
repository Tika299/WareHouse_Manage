<?php

namespace App\Http\Controllers;

use App\Models\ShippingUnit;
use Illuminate\Http\Request;

class ShippingUnitController extends Controller
{
    public function index()
    {
        $units = ShippingUnit::latest()->get();
        return view('shipping_units.index', [
            'units' => $units,
            'activeGroup' => 'sales',
            'activeName' => 'shipping'
        ]);
    }

    public function create()
    {
        return view('shipping_units.create', [
            'activeGroup' => 'sales',
            'activeName' => 'shipping'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        ShippingUnit::create($request->all());

        return redirect()->route('shipping-units.index')->with('msg', 'Thêm đơn vị vận chuyển thành công!');
    }

    public function edit(ShippingUnit $shipping_unit)
    {
        return view('shipping_units.edit', [
            'unit' => $shipping_unit,
            'activeGroup' => 'sales',
            'activeName' => 'shipping'
        ]);
    }

    public function update(Request $request, ShippingUnit $shipping_unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $shipping_unit->update($request->all());

        return redirect()->route('shipping-units.index')->with('msg', 'Cập nhật thành công!');
    }

    public function destroy(ShippingUnit $shipping_unit)
    {
        // Kiểm tra nếu có đơn hàng đang sử dụng ĐVVC này thì không cho xóa
        if ($shipping_unit->salesOrders()->count() > 0) {
            return back()->with('warning', 'Không thể xóa đơn vị này vì đã có đơn hàng sử dụng!');
        }

        $shipping_unit->delete();
        return back()->with('msg', 'Đã xóa đơn vị vận chuyển!');
    }
}
