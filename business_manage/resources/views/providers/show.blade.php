@extends('layouts.app')
@section('title', 'Lịch sử nợ Nhà cung cấp')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center font-weight-bold">{{ $provider->name }}</h3>
                <p class="text-muted text-center">Số điện thoại: {{ $provider->phone }}</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Tổng nợ hiện tại</b> 
                        <a class="float-right text-danger text-bold">{{ number_format($provider->total_debt) }} đ</a>
                    </li>
                </ul>
                <a href="{{ route('vouchers.index') }}" class="btn btn-danger btn-block"><b>Lập phiếu chi trả nợ</b></a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nhật ký biến động nợ (Credit Log)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead class="bg-light text-13">
                        <tr>
                            <th>Ngày giờ</th>
                            <th>Mã phiếu</th>
                            <th>Nội dung</th>
                            <th class="text-right">Biến động</th>
                            <th class="text-right">Dư nợ cuối</th>
                        </tr>
                    </thead>
                    <tbody class="text-12">
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-secondary">{{ $log->ref_type }} #{{ $log->ref_id }}</span></td>
                            <td>{{ $log->note }}</td>
                            <td class="text-right {{ $log->change_amount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $log->change_amount > 0 ? '+' : '' }}{{ number_format($log->change_amount) }}
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format($log->new_balance) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection