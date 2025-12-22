@extends('layouts.app')
@section('title', 'Quản lý Sổ quỹ')
@section('content')
<div class="row">
    <div class="col-md-12 text-right mb-3">
        <a href="{{ route('internal_transfers.create') }}" class="btn btn-warning font-weight-bold">
            <i class="fas fa-exchange-alt"></i> Chuyển khoản nội bộ
        </a>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary font-weight-bold">
            <i class="fas fa-plus"></i> Thêm tài khoản/ngân hàng
        </a>
    </div>
</div>

<div class="row">
    @foreach($accounts as $acc)
    <div class="col-md-4">
        <div class="small-box {{ $acc->type == 'cash' ? 'bg-success' : 'bg-info' }}">
            <div class="inner">
                <h3>{{ number_format($acc->current_balance) }} <small>đ</small></h3>
                <p>{{ $acc->name }} ({{ $acc->type == 'cash' ? 'Tiền mặt' : 'Ngân hàng' }})</p>
            </div>
            <div class="icon"><i class="fas {{ $acc->type == 'cash' ? 'fa-wallet' : 'fa-university' }}"></i></div>
            <a href="{{ route('accounts.show', $acc->id) }}" class="small-box-footer">Xem sổ chi tiết <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endforeach
</div>
@endsection