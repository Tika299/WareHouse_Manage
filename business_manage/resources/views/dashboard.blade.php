@extends('layouts.app')

@section('title', 'Bảng điều khiển quản trị')

@section('content')
<div class="container-fluid pt-3">
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-3">Chào mừng trở lại, <b>{{ auth()->user()->name }}</b>!</h4>
        </div>
    </div>

    <!-- HÀNG 1: WIDGETS -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h3>{{ number_format($todayRevenue) }}<small>đ</small></h3>
                    <p>Doanh thu hôm nay ({{ $todayOrders }} đơn)</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('exports.index') }}" class="small-box-footer">Đơn hàng <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow">
                <div class="inner">
                    <h3>{{ $lowStockCount }}</h3>
                    <p>Sản phẩm sắp hết hàng!</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('products.index') }}?stock_status=low_stock" class="small-box-footer">Kiểm kho <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ number_format($totalCustomerDebt) }}<small>đ</small></h3>
                    <p>Tổng nợ Khách nợ mình</p>
                </div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
                <a href="{{ route('reportDebt') }}" class="small-box-footer">Đối soát nợ <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow">
                <div class="inner">
                    <h3>{{ number_format($totalCash) }}<small>đ</small></h3>
                    <p>Tiền hiện có (Quỹ + Bank)</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
                <a href="{{ route('accounts.index') }}" class="small-box-footer">Sổ quỹ <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- BIỂU ĐỒ DOANH THU -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-chart-area"></i> Xu hướng doanh thu 7 ngày</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- TRẠNG THÁI VÍ TIỀN -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark"><h3 class="card-title">Phân bổ tiền mặt/ngân hàng</h3></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($accounts as $acc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas {{ $acc->type == 'cash' ? 'fa-money-bill-wave text-success' : 'fa-university text-info' }}"></i> {{ $acc->name }}</span>
                            <span class="badge badge-light border">{{ number_format($acc->current_balance) }} đ</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm bg-primary">
                <div class="card-body text-center">
                    <h6>TỔNG GIÁ TRỊ TỒN KHO (VỐN)</h6>
                    <h3>{{ number_format($totalStockValue) }} đ</h3>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Lấy dữ liệu mảng từ PHP gửi qua (An toàn tuyệt đối)
        const labels = @json($chartLabels);
        const totals = @json($chartTotals);

        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: totals,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5,
                        pointBackgroundColor: '#007bff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection