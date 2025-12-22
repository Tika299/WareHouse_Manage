@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Thông tin tài khoản</h3></div>
            <div class="card-body">
                <form>
                    <div class="form-group"><label>Họ tên</label><input type="text" class="form-control" value="{{ auth()->user()->name }}"></div>
                    <div class="form-group"><label>Email</label><input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled></div>
                    <div class="form-group"><label>Đổi mật khẩu mới</label><input type="password" class="form-control"></div>
                    <button class="btn btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection