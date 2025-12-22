@extends('layouts.app')
@section('title', 'Sổ chi tiết: ' . $account->name)
@section('content')
<div class="card">
    <div class="card-header bg-light">
        <h3 class="card-title">Lịch sử giao dịch: <b>{{ $account->name }}</b></h3>
        <div class="card-tools">Số dư hiện tại: <b class="text-danger h5">{{ number_format($account->current_balance) }} đ</b></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Ngày/Giờ</th>
                    <th>Loại</th>
                    <th>Hạng mục</th>
                    <th class="text-right">Số tiền</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vouchers as $v)
                <tr>
                    <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $v->voucher_type == 'receipt' ? 'badge-success' : 'badge-danger' }}">
                            {{ $v->voucher_type == 'receipt' ? 'THU' : 'CHI' }}
                        </span>
                    </td>
                    <td>{{ $v->category }}</td>
                    <td class="text-right font-weight-bold {{ $v->voucher_type == 'receipt' ? 'text-success' : 'text-danger' }}">
                        {{ $v->voucher_type == 'receipt' ? '+' : '-' }} {{ number_format($v->amount) }}
                    </td>
                    <td>{{ $v->note }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $vouchers->links() }}</div>
</div>
@endsection