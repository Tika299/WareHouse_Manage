@extends('layouts.app')
@section('title', 'Đối soát nợ: ' . $customer->name)
@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <h3 class="profile-username font-weight-bold">{{ $customer->name }}</h3>
                <p class="text-muted">{{ $customer->phone }}</p>
                <div class="bg-light p-3 rounded border">
                    <small class="text-uppercase text-muted">Tổng nợ gộp hiện tại</small>
                    <h3 class="text-danger font-weight-bold mb-0">{{ number_format($customer->total_debt) }} đ</h3>
                </div>
                <a href="{{ route('vouchers.create') }}?customer_id={{ $customer->id }}" class="btn btn-success btn-block mt-3"><b><i class="fas fa-hand-holding-usd"></i> Thu nợ khách hàng</b></a>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Nhật ký biến động nợ (Credit Log)</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead class="bg-light">
                        <tr class="text-13">
                            <th>Ngày giờ</th>
                            <th>Mã phiếu</th>
                            <th>Lý do / Nội dung</th>
                            <th class="text-right">Biến động</th>
                            <th class="text-right">Dư nợ sau GD</th>
                        </tr>
                    </thead>
                    <tbody class="text-12">
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ strtoupper($log->ref_type) }} #{{ $log->ref_id }}</span>
                            </td>
                            <td>{{ $log->note }}</td>
                            <td class="text-right font-weight-bold {{ $log->change_amount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $log->change_amount > 0 ? '+' : '' }}{{ number_format($log->change_amount) }}
                            </td>
                            <td class="text-right font-weight-bold">{{ number_format($log->new_balance) }} đ</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center p-4">Chưa có lịch sử giao dịch nợ.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
@endsection