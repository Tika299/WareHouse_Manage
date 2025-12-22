<?php

// app/Http/Controllers/CustomerController.php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(15);
        return view('customers.index', compact('customers'), [
            'activeGroup' => 'sales',
            'activeName' => 'customers'
        ]);
    }

    public function create()
    {
        return view('customers.create', [
            'activeGroup' => 'sales',
            'activeName' => 'customers'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|max:20',
        ]);

        Customer::create($request->all());
        return redirect()->route('customers.index')->with('msg', 'Thêm khách hàng thành công!');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'), [
            'activeGroup' => 'sales',
            'activeName' => 'customers'
        ]);
    }

    /**
     * TRANG ĐỐI SOÁT NỢ (Cực kỳ quan trọng)
     */
    public function show(Customer $customer)
    {
        // Lấy lịch sử nợ từ CreditLog
        $logs = $customer->creditLogs()->paginate(20);

        return view('customers.show', compact('customer', 'logs'), [
            'activeGroup' => 'sales',
            'activeName' => 'customers'
        ]);
    }

    public function destroy(Customer $customer)
    {
        // SPEC: Không cho xóa nếu khách còn nợ
        if ($customer->total_debt > 0) {
            return back()->with('warning', 'Không thể xóa khách hàng đang còn nợ gộp!');
        }
        $customer->delete();
        return back()->with('msg', 'Đã xóa khách hàng!');
    }

    public function update(Request $request, Customer $customer)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
        ]);

        // 2. Cập nhật dữ liệu (Chỉ cập nhật 3 trường này, ko cho sửa nợ trực tiếp)
        $customer->update($request->only(['name', 'phone', 'address']));

        // 3. Quay lại trang danh sách kèm thông báo
        return redirect()->route('customers.index')->with('msg', 'Cập nhật thông tin khách hàng thành công!');
    }
}
