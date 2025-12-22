@extends('layouts.app')
@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-header bg-primary">
        <h3 class="card-title">{{ isset($unit) ? 'Chỉnh sửa đơn vị' : 'Thêm đơn vị vận chuyển' }}</h3>
    </div>
    <form action="{{ isset($unit) ? route('shipping_units.update', $unit->id) : route('shipping_units.store') }}" method="POST">
        @csrf
        @if(isset($unit)) @method('PUT') @endif
        <div class="card-body">
            <div class="form-group">
                <label>Tên đơn vị vận chuyển *</label>
                <input type="text" name="name" class="form-control" value="{{ $unit->name ?? '' }}" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại liên hệ</label>
                <input type="text" name="phone" class="form-control" value="{{ $unit->phone ?? '' }}">
            </div>
            <div class="form-group">
                <label>Địa chỉ văn phòng / Kho bãi</label>
                <textarea name="address" class="form-control" rows="2">{{ $unit->address ?? '' }}</textarea>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('shipping_units.index') }}" class="btn btn-default">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu thông tin</button>
        </div>
    </form>
</div>
@endsection