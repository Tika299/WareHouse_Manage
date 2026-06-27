@extends('layouts.app')

@section('title', 'Cập nhật tài khoản')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title mb-0 font-weight-bold">
                    <i class="fas fa-edit"></i> Cập nhật tài khoản
                </h3>
            </div>

            <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="form-group">
                        <label>Tên tài khoản <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $account->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Loại tài khoản <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="cash" {{ old('type', $account->type) == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                            <option value="bank" {{ old('type', $account->type) == 'bank' ? 'selected' : '' }}>Ngân hàng</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Số dư ban đầu <span class="text-danger">*</span></label>
                        <input type="number" name="initial_balance" class="form-control"
                               value="{{ old('initial_balance', $account->initial_balance) }}" min="0" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Số dư hiện tại <span class="text-danger">*</span></label>
                        <input type="number" name="current_balance" class="form-control"
                               value="{{ old('current_balance', $account->current_balance) }}" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection