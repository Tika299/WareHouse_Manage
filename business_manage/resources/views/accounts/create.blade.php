@extends('layouts.app')

@section('title', 'Thêm tài khoản mới')

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf
            <div class="card card-primary card-outline shadow">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Khởi tạo Tài khoản / Sổ quỹ</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Tên tài khoản <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Ví dụ: Tiền mặt, Vietcombank, Momo..." 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="type">Loại tài khoản</label>
                        <select name="type" id="type" class="form-control">
                            <option value="cash">Tiền mặt (Quỹ giấy)</option>
                            <option value="bank">Tài khoản Ngân hàng / Điện tử</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="initial_balance">Số dư ban đầu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="initial_balance" id="initial_balance" 
                                   class="form-control form-control-lg text-bold text-success" 
                                   placeholder="0" value="{{ old('initial_balance', 0) }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <small class="text-muted italic">
                            <i class="fas fa-info-circle"></i> SPEC: Số tiền này sẽ được nạp vào làm số dư gốc. Bạn chỉ có thể nhập 1 lần duy nhất lúc này.
                        </small>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('accounts.index') }}" class="btn btn-default px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Lưu tài khoản</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection