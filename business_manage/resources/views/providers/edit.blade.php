@extends('layouts.app')

@section('title', 'Chỉnh sửa Nhà cung cấp')

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Chỉnh sửa Nhà cung cấp: {{ $provider->name }}</h3>
            </div>
            <!-- Form bắt đầu -->
            <form action="{{ route('providers.update', $provider->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Bắt buộc phải có để Laravel hiểu đây là lệnh cập nhật --}}
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $provider->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" name="phone" id="phone" class="form-control" 
                               value="{{ old('phone', $provider->phone) }}">
                    </div>

                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $provider->address) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Công nợ hiện tại</label>
                        <input type="text" class="form-control bg-light" 
                               value="{{ number_format($provider->total_debt) }} đ" readonly>
                        <small class="text-muted italic">Lưu ý: Công nợ chỉ thay đổi qua Phiếu nhập hoặc Phiếu chi.</small>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('providers.index') }}" class="btn btn-default">Quay lại</a>
                    <button type="submit" class="btn btn-warning font-weight-bold">Cập nhật thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection