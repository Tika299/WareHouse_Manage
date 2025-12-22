@extends('layouts.app')
@section('title', 'Báo cáo Doanh thu & Thanh toán')

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reportPayments') }}" method="GET" class="form-inline">
                <label class="mr-2">Thời gian:</label>
                <select name="month" class="form-control mr-2 font-weight-bold">
                    @for($m=1; $m<=12; $m++) 
                        <option value="{{$m}}" {{$month == $m ? 'selected' : ''}}>Tháng {{$m}}</option> 
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary">Xem thống kê</button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- TIỀN THU TỪ ĐƠN HÀNG -->
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title font-weight-bold">Tiền thu trực tiếp từ Đơn hàng</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Phương thức / Tài khoản</th><th class="text-right">Số tiền thu</th></tr>
                        </thead>
                        <tbody>
                            @foreach($paymentStats as $stat)
                            <tr>
                                <td><i class="fas fa-wallet text-info"></i> {{ $stat->account_name }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($stat->total_collected) }} đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TIỀN THU TỪ PHIẾU THU (TRẢ NỢ GỘP) -->
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title font-weight-bold">Tiền thu từ Phiếu Thu (Trả nợ)</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Phương thức / Tài khoản</th><th class="text-right">Số tiền thu</th></tr>
                        </thead>
                        <tbody>
                            @foreach($voucherStats as $vStat)
                            <tr>
                                <td><i class="fas fa-university text-success"></i> {{ $vStat->account_name }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($vStat->total_collected) }} đ</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-dark">
                <div class="card-body text-center">
                    <h4 class="text-muted">TỔNG TIỀN THỰC THU TRONG THÁNG {{ $month }}:</h4>
                    <h1 class="text-success font-weight-bold">
                        {{ number_format($paymentStats->sum('total_collected') + $voucherStats->sum('total_collected')) }} đ
                    </h1>
                    <p><i>(Bao gồm tiền khách mua đơn mới và tiền khách trả nợ cũ)</i></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection