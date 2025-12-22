@extends('layouts.app')
@section('content')
<div class="card col-md-6 mx-auto">
    <div class="card-header bg-success text-white">
        <h3 class="card-title">Lập Phiếu Thu (Thu nợ gộp)</h3>
    </div>
    <form action="{{ route('vouchers.store') }}" method="POST">
        @csrf
        <input type="hidden" name="voucher_type" value="receipt">
        <div class="card-body">
            <div class="form-group">
                <label>Chọn khách hàng trả nợ</label>
                <select name="customer_id" class="form-control select2">
                    <option value="">-- Tìm khách hàng --</option>
                </select>
                <div class="mt-2 text-danger">Tổng nợ hiện tại: <b id="current-debt">0đ</b></div>
            </div>
            <div class="form-group">
                <label>Số tiền thu</label>
                <input type="number" name="amount" class="form-control form-control-lg" placeholder="Nhập số tiền khách trả...">
            </div>
            <div class="form-group">
                <label>Thu vào tài khoản nào?</label>
                <select name="account_id" class="form-control">
                    <option value="1">Tiền mặt (Quỹ chính)</option>
                    <option value="2">Tài khoản Ngân hàng</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="note" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success btn-block">XÁC NHẬN THU TIỀN</button>
        </div>
    </form>
</div>
@endsection