@extends('layouts.app')
@section('title', 'Báo cáo quản trị')
@section('content')
<div class="container-fluid">
    <!-- Bộ lọc -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reportOverview') }}" method="GET" class="form-inline">
                <label class="mr-2">Xem báo cáo tháng:</label>
                <select name="month" class="form-control mr-2">
                    @for($m=1; $m<=12; $m++) <option value="{{$m}}" {{$month == $m ? 'selected' : ''}}>Tháng {{$m}}</option> @endfor
                </select>
                <select name="year" class="form-control mr-2">
                    <option value="2025">2025</option>
                </select>
                <button type="submit" class="btn btn-primary">Xem báo cáo</button>
            </form>
        </div>
    </div>

    <!-- Hộp thống kê (Widget) -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($totalRevenue) }}<small>đ</small></h3><p>Doanh thu thuần</p></div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ number_format($totalCogs) }}<small>đ</small></h3><p>Giá vốn hàng bán</p></div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ number_format($grossProfit) }}<small>đ</small></h3><p>Lợi nhuận gộp</p></div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner"><h3>{{ number_format($netProfit) }}<small>đ</small></h3><p>Lãi thực (Lợi nhuận ròng)</p></div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biểu đồ -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> Doanh thu theo tháng</h3></div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <!-- Chi tiết chi phí -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title text-bold">Cấu trúc Chi phí & Lợi nhuận</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <tbody>
                            <tr><td>Lợi nhuận gộp</td><td class="text-right text-bold text-primary">{{ number_format($grossProfit) }}</td></tr>
                            <tr><td>Chi phí vận hành</td><td class="text-right text-danger">- {{ number_format($operationalExpenses) }}</td></tr>
                            <tr><td>Phí ship (Shop trả)</td><td class="text-right text-danger">- {{ number_format($shopShippingFees) }}</td></tr>
                            <tr class="bg-light">
                                <td class="text-bold">Lợi nhuận ròng</td>
                                <td class="text-right text-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($netProfit) }} đ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData->pluck('month')->map(fn($m) => 'Tháng '.$m)) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode($chartData->pluck('revenue')) !!},
                backgroundColor: 'rgba(60,141,188,0.9)',
            }]
        },
        options: { maintainAspectRatio: false, responsive: true }
    });
</script>
@endpush
@endsection