<?php

// app/Http/Controllers/CreditLogController.php
namespace App\Http\Controllers;

use App\Models\CreditLog;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class CreditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditLog::query();

        // 1. Lọc theo loại đối tượng (Khách hàng/NCC)
        if ($request->has('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // 2. Lọc theo ID cụ thể (Khi bấm từ trang chi tiết khách sang)
        if ($request->has('target_id')) {
            $query->where('target_id', $request->target_id);
        }

        // 3. Lọc theo ngày tháng
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Lấy danh sách để làm bộ lọc trên giao diện
        $customers = Customer::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();

        return view('credit_logs.index', compact('logs', 'customers', 'suppliers'), [
            'activeGroup' => 'finance',
            'activeName' => 'credit_logs'
        ]);
    }
}
