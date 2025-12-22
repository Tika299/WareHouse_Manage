@extends('layouts.app')
@section('content')
<div class="card col-md-6 mx-auto shadow">
    <div class="card-header bg-warning">
        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-exchange-alt"></i> Lệnh Chuyển Khoản Nội Bộ</h3>
    </div>
    <form action="{{ route('internal_transfers.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Tài khoản nguồn (Trích tiền)</label>
                <select name="from_account_id" class="form-control">
                    @foreach($accounts as $a) <option value="{{$a->id}}">{{$a->name}} (Dư: {{number_format($a->current_balance)}}đ)</option> @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tài khoản đích (Nhận tiền)</label>
                <select name="to_account_id" class="form-control">
                    @foreach($accounts as $a) <option value="{{$a->id}}">{{$a->name}} (Dư: {{number_format($a->current_balance)}}đ)</option> @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Số tiền muốn chuyển</label>
                <input type="number" name="amount" class="form-control form-control-lg text-danger" required>
            </div>
            <div class="form-group">
                <label>Lý do chuyển</label>
                <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Rút tiền mặt gửi ngân hàng..."></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning btn-block btn-lg"><b>THỰC HIỆN CHUYỂN TIỀN</b></button>
        </div>
    </form>
</div>
@endsection