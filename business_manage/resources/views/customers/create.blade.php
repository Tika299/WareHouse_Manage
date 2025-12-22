@extends('layouts.app')
@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-header bg-primary"><h3 class="card-title">Thông tin khách hàng</h3></div>
    <form action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}" method="POST">
        @csrf
        @if(isset($customer)) @method('PUT') @endif
        <div class="card-body">
            <div class="form-group">
                <label>Tên khách hàng *</label>
                <input type="text" name="name" class="form-control" value="{{ $customer->name ?? '' }}" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại *</label>
                <input type="text" name="phone" class="form-control" value="{{ $customer->phone ?? '' }}" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="address" class="form-control" rows="2">{{ $customer->address ?? '' }}</textarea>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('customers.index') }}" class="btn btn-default">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu khách hàng</button>
        </div>
    </form>
</div>
@endsection