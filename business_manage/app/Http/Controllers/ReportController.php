<?php

namespace App\Http\Controllers;

use App\Models\{SalesOrder, SalesDetail, CashVoucher, Product, PurchaseOrder};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tháng/năm lọc, mặc định là tháng hiện tại
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        // 1. Tính Doanh thu (Sales)
        $totalRevenue = SalesOrder::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_final_amount');

        // 2. Tính Giá vốn hàng bán (COGS)
        $totalCogs = DB::table('sales_details')
            ->join('sales_orders', 'sales_details.sales_order_id', '=', 'sales_orders.id')
            ->whereYear('sales_orders.created_at', $year)
            ->whereMonth('sales_orders.created_at', $month)
            ->sum(DB::raw('quantity * cost_price_at_sale'));

        // 3. Tính Chi phí vận hành (Operational Expenses từ Phiếu Chi)
        $operationalExpenses = CashVoucher::where('voucher_type', 'payment')
            ->where('category', 'operational')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');

        // 4. Tính Phí ship mà Shop phải chịu (Trừ vào lãi)
        $shopShippingFees = SalesOrder::where('shipping_payor', 'shop')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('shipping_fee');

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $operationalExpenses - $shopShippingFees;

        // Dữ liệu biểu đồ 12 tháng (Minh họa logic)
        $chartData = $this->getMonthlyRevenueData($year);

        return view('reports.overview', compact(
            'totalRevenue',
            'totalCogs',
            'operationalExpenses',
            'shopShippingFees',
            'grossProfit',
            'netProfit',
            'chartData',
            'month',
            'year'
        ), [
            'activeGroup' => 'reports',
            'activeName' => 'overview'
        ]);
    }

    private function getMonthlyRevenueData($year)
    {
        return SalesOrder::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_final_amount) as revenue')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Báo cáo Xuất Nhập Tồn
     */
    public function exportImport()
    {
        $products = Product::all();
        return view('reports.export_import', compact('products'), [
            'activeGroup' => 'reports',
            'activeName' => 'export_import'
        ]);
    }

    public function paymentReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Thống kê số tiền thu được từ Đơn hàng theo từng tài khoản
        $paymentStats = DB::table('sales_orders')
            ->join('accounts', 'sales_orders.account_id', '=', 'accounts.id')
            ->select('accounts.name as account_name', DB::raw('SUM(paid_amount) as total_collected'))
            ->whereYear('sales_orders.created_at', $year)
            ->whereMonth('sales_orders.created_at', $month)
            ->groupBy('accounts.id', 'accounts.name')
            ->get();

        // Thống kê số tiền thu từ Phiếu Thu (Vouchers) - để tính nợ gộp khách trả
        $voucherStats = DB::table('cash_vouchers')
            ->join('accounts', 'cash_vouchers.account_id', '=', 'accounts.id')
            ->select('accounts.name as account_name', DB::raw('SUM(amount) as total_collected'))
            ->where('voucher_type', 'receipt')
            ->whereYear('cash_vouchers.created_at', $year)
            ->whereMonth('cash_vouchers.created_at', $month)
            ->groupBy('accounts.id', 'accounts.name')
            ->get();

        return view('reports.payments', compact('paymentStats', 'voucherStats', 'month', 'year'), [
            'activeGroup' => 'reports',
            'activeName' => 'payments'
        ]);
    }

    public function debtReport(Request $request)
    {
        // 1. Lấy danh sách khách hàng đang nợ (total_debt > 0)
        // Sắp xếp người nợ nhiều nhất lên đầu
        $customerDebts = \App\Models\Customer::where('total_debt', '>', 0)
            ->orderBy('total_debt', 'desc')
            ->get();

        // 2. Lấy danh sách nhà cung cấp mình đang nợ (total_debt > 0)
        $supplierDebts = \App\Models\Supplier::where('total_debt', '>', 0)
            ->orderBy('total_debt', 'desc')
            ->get();

        // 3. Tính tổng các con số để hiển thị Widget
        $totalReceivable = $customerDebts->sum('total_debt'); // Tổng phải thu
        $totalPayable = $supplierDebts->sum('total_debt');    // Tổng phải trả

        return view('reports.debt', compact('customerDebts', 'supplierDebts', 'totalReceivable', 'totalPayable'), [
            'activeGroup' => 'reports',
            'activeName' => 'debt'
        ]);
    }
}
