@extends('layouts.app')

@section('title', 'Chỉnh sửa khách hàng')

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Chỉnh sửa khách hàng: {{ $customer->name }}</h3>
            </div>
            
            <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Bắt buộc phải có để Laravel hiểu là lệnh cập nhật --}}
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $customer->phone) }}" required>
                        @error('phone')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Địa chỉ</label>
                        <textarea name="address" id="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Tổng nợ hiện tại (Nợ gộp)</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light font-weight-bold text-danger" 
                                   value="{{ number_format($customer->total_debt) }} đ" readonly>
                            <div class="input-group-append">
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-info">Xem chi tiết nợ</a>
                            </div>
                        </div>
                        <small class="text-muted italic">Lưu ý: Không thể sửa trực tiếp con số nợ ở đây. Vui lòng lập Phiếu Thu hoặc Tạo đơn hàng để thay đổi nợ.</small>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('customers.index') }}" class="btn btn-default px-4">Hủy</a>
                    <button type="submit" class="btn btn-warning px-4 font-weight-bold">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection