@extends('layouts.app')
@section('content')
<div class="card col-md-8 mx-auto">
    <div class="card-header bg-warning"><h3 class="card-title text-dark">Lệnh Chuyển khoản nội bộ</h3></div>
    <div class="card-body">
        <form>
            <div class="form-group">
                <label>Tài khoản nguồn (Trích tiền)</label>
                <select class="form-control"><option>Tiền mặt (Quỹ chính)</option></select>
            </div>
            <div class="form-group">
                <label>Tài khoản đích (Nhận tiền)</label>
                <select class="form-control"><option>Ngân hàng Vietcombank</option></select>
            </div>
            <div class="form-group">
                <label>Số tiền chuyển</label>
                <input type="number" class="form-control" placeholder="0.00">
            </div>
            <button class="btn btn-warning btn-block">Xác nhận chuyển tiền</button>
        </form>
    </div>
</div>
@endsection