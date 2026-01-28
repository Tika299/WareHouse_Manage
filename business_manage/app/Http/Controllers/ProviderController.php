<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Traits\Select2Searchable;

class ProviderController extends Controller
{
    use Select2Searchable;

    public function index()
    {
        $suppliers = Supplier::all();
        return view('providers.index', [
            'suppliers' => $suppliers,
            'activeGroup' => 'finance',
            'activeName' => 'providers'
        ]);
    }

    public function create()
    {
        return view('providers.create', [
            'activeGroup' => 'finance',
            'activeName' => 'providers'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $supplier = \App\Models\Supplier::create($request->all());

        // Nếu là yêu cầu AJAX từ trang Nhập hàng
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Thêm NCC thành công!'
            ]);
        }

        // Nếu là yêu cầu bình thường từ trang quản lý NCC
        return redirect()->route('providers.index')->with('msg', 'Thêm nhà cung cấp thành công!');
    }

    public function edit(Supplier $provider)
    {
        return view('providers.edit', [
            'provider' => $provider,
            'activeGroup' => 'finance',
            'activeName' => 'providers'
        ]);
    }

    public function update(Request $request, Supplier $provider)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $provider->update($request->all());

        return redirect()->route('providers.index')->with('msg', 'Cập nhật thành công!');
    }

    /**
     * Trang chi tiết NCC & Đối soát công nợ
     */
    public function show(Supplier $provider)
    {
        $logs = $provider->creditLogs()->paginate(15);

        return view('providers.show', [
            'provider' => $provider,
            'logs' => $logs,
            'activeGroup' => 'systemFirst',
            'activeName' => 'providers'
        ]);
    }

    public function destroy(Supplier $provider)
    {
        if ($provider->total_debt != 0) {
            return back()->with('warning', 'Không thể xóa NCC khi còn nợ chưa thanh toán!');
        }

        $provider->delete();
        return back()->with('msg', 'Đã xóa nhà cung cấp!');
    }

    public function searchAjax(Request $request)
    {
        return $this->performSelect2Search($request, Supplier::class, ['name', 'phone']);
    }
}
