@extends('layouts.app')
@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-header bg-primary"><h3 class="card-title">Thêm nhà cung cấp mới</h3></div>
    <form action="{{ route('providers.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Tên nhà cung cấp <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Nhập tên NCC..." required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('providers.index') }}" class="btn btn-default">Quay lại</a>
            <button type="submit" class="btn btn-primary">Lưu thông tin</button>
        </div>
    </form>
</div>
@endsection