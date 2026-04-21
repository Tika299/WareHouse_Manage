<?php

namespace App\Http\Controllers;

use App\Models\{Product, SalesOrder, Customer, Supplier, Account, CashVoucher};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Thống kê hôm nay
        $todayRevenue = SalesOrder::whereDate('created_at', $today)->sum('total_final_amount') ?? 0;
        $todayOrders = SalesOrder::whereDate('created_at', $today)->count() ?? 0;

        // 2. Thống kê Kho hàng
        $totalProducts = Product::count();
        $lowStockCount = Product::whereIn('product_type', ['single', 'variant'])
            ->whereRaw('stock_quantity <= min_stock')
            ->count();

        // Tính tổng giá trị tồn kho (Vốn)
        $totalStockValue = Product::whereIn('product_type', ['single', 'variant'])
            ->where('stock_quantity', '>', 0)
            ->selectRaw('SUM(stock_quantity * cost_price) as total')
            ->first()->total ?? 0;

        // 3. Thống kê Công nợ
        $totalCustomerDebt = Customer::sum('total_debt') ?? 0;
        $totalSupplierDebt = Supplier::sum('total_debt') ?? 0;

        // 4. Sổ quỹ
        $accounts = Account::all();
        $totalCash = $accounts->sum('current_balance') ?? 0;

        // 5. Chuẩn bị dữ liệu Biểu đồ (7 ngày gần nhất)
        $chartData = SalesOrder::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_final_amount) as total')
        )
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Tách dữ liệu ra mảng phẳng để gửi sang JavaScript (Fix lỗi ParseError)
        $chartLabels = $chartData->pluck('date')->map(fn($d) => date('d/m', strtotime($d)))->toArray();
        $chartTotals = $chartData->pluck('total')->toArray();

        // 6. Đơn hàng mới nhất
        $recentOrders = SalesOrder::with('customer')->latest()->limit(5)->get();

        // TRUYỀN TẤT CẢ BIẾN SANG VIEW
        return view('dashboard', [
            'todayRevenue'      => $todayRevenue,
            'todayOrders'       => $todayOrders,
            'lowStockCount'     => $lowStockCount,
            'totalStockValue'   => $totalStockValue,
            'totalCustomerDebt' => $totalCustomerDebt,
            'totalSupplierDebt' => $totalSupplierDebt,
            'totalCash'         => $totalCash,
            'accounts'          => $accounts,
            'recentOrders'      => $recentOrders,
            'chartLabels'       => $chartLabels,
            'chartTotals'       => $chartTotals,
            'activeGroup'       => 'dashboard',
            'activeName'        => ''
        ]);
    }
}
