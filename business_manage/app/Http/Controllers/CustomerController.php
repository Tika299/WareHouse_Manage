<?php

// app/Http/Controllers/CustomerController.php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\Select2Searchable;

class CustomerController extends Controller
{
    use Select2Searchable;
    public function index(Request $request)
    {
        $query = Customer::select('id', 'name', 'phone', 'address', 'total_debt', 'created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                // Sử dụng LIKE để tìm kiếm linh hoạt hơn cho tên người
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Lọc nợ (giữ nguyên)
        if ($request->filled('debt_from')) {
            $query->where('total_debt', '>=', (float) $request->debt_from);
        }
        if ($request->filled('debt_to')) {
            $query->where('total_debt', '<=', (float) $request->debt_to);
        }

        // Sắp xếp và phân trang
        $customers = $query->orderByDesc('total_debt')
            ->paginate(15)
            ->withQueryString();

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

    public function searchAjax(Request $request)
    {
        return $this->performSelect2Search($request, \App\Models\Customer::class, ['name', 'phone']);
    }
}
