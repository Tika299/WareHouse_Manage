@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thêm sản phẩm mới</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Mã SKU (Mã vạch)</label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="Ví dụ: SP001" required>
                            @error('sku') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nhập tên sản phẩm..." required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Đơn vị tính</label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', 'Cái') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Tồn kho tối thiểu</label>
                            <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 5) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Giá vốn khởi tạo</label>
                            <input type="number" id="cost_price" name="cost_price" class="form-control" value="{{ old('cost_price', 0) }}">
                            <small class="text-muted">Sau này sẽ tự cập nhật theo BQGQ.</small>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Mức cộng Lẻ (Nhập tay)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">+</span></div>
                                <input type="number" id="markup_retail" name="markup_retail" class="form-control" value="{{ old('markup_retail', 0) }}">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mức cộng Sỉ (Nhập tay)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">+</span></div>
                                <input type="number" id="markup_wholesale" name="markup_wholesale" class="form-control" value="{{ old('markup_wholesale', 0) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Khu vực hiển thị giá bán dự kiến --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text text-primary font-weight-bold">GIÁ BÁN LẺ DỰ KIẾN</span>
                                    <span class="info-box-number h3" id="preview_retail">0 đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text text-orange font-weight-bold">GIÁ BÁN SỈ DỰ KIẾN</span>
                                    <span class="info-box-number h3" id="preview_wholesale">0 đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('products.index') }}" class="btn btn-default">Quay lại</a>
                    <button type="submit" class="btn btn-primary px-4">Lưu sản phẩm</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function calculatePrices() {
        // Lấy giá trị, mặc định là 0 nếu trống
        let cost = parseFloat($('#cost_price').val()) || 0;
        let markupRetail = parseFloat($('#markup_retail').val()) || 0;
        let markupWholesale = parseFloat($('#markup_wholesale').val()) || 0;

        // Tính toán theo SPEC: Giá bán = Giá vốn + Mức cộng
        let retail = cost + markupRetail;
        let wholesale = cost + markupWholesale;

        // Hiển thị định dạng tiền tệ VNĐ
        $('#preview_retail').text(new Intl.NumberFormat('vi-VN').format(retail) + ' đ');
        $('#preview_wholesale').text(new Intl.NumberFormat('vi-VN').format(wholesale) + ' đ');
    }

    $(document).ready(function() {
        // Chạy lần đầu
        calculatePrices();

        // Lắng nghe sự kiện nhập liệu
        $('#cost_price, #markup_retail, #markup_wholesale').on('input', function() {
            calculatePrices();
        });
    });
</script>
@endpush
@endsection