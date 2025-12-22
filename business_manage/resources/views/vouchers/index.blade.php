@extends('layouts.app')
@section('title', 'Sổ nhật ký Thu Chi')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Nhật ký giao dịch tiền mặt/ngân hàng</h3>
        <a href="{{ route('vouchers.create') }}" class="btn btn-success btn-sm ml-auto">+ Lập phiếu mới</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light text-13">
                    <th>Ngày/Giờ</th>
                    <th>Loại</th>
                    <th>Đối tượng</th>
                    <th>Hạng mục</th>
                    <th>Tài khoản</th>
                    <th class="text-right">Số tiền</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($vouchers as $v)
                <tr>
                    <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $v->voucher_type == 'receipt' ? 'badge-success' : 'badge-danger' }}">
                            {{ $v->voucher_type == 'receipt' ? 'THU' : 'CHI' }}
                        </span>
                    </td>
                    <td>
                        {{ $v->customer->name ?? ($v->supplier->name ?? 'Nội bộ') }}
                    </td>
                    <td>{{ str_replace('_', ' ', $v->category) }}</td>
                    <td>{{ $v->account->name }}</td>
                    <td class="text-right font-weight-bold {{ $v->voucher_type == 'receipt' ? 'text-success' : 'text-danger' }}">
                        {{ $v->voucher_type == 'receipt' ? '+' : '-' }} {{ number_format($v->amount) }} đ
                    </td>
                    <td class="text-muted">{{ $v->note }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $vouchers->links() }}</div>
</div>
@endsection