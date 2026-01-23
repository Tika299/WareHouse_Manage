@extends('layouts.app')

@section('title', isset($product) ? 'Chỉnh sửa: ' . $product->name : 'Thêm sản phẩm mới')

@section('content')
<div class="container-fluid">
    <form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="row">
            <!-- CỘT TRÁI: THÔNG TIN CƠ BẢN -->
            <div class="col-md-5">
                <div class="card card-outline card-primary shadow">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Thông tin sản phẩm</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Mã SKU (Mã vạch) <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                                value="{{ old('sku', $product->sku ?? '') }}" required {{ isset($product) ? 'readonly' : '' }}>
                            @error('sku') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Đơn vị tính</label>
                                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'Cái') }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Tồn tối thiểu</label>
                                    <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $product->min_stock ?? 5) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="text-primary">Giá vốn hiện tại (BQGQ)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control font-weight-bold"
                                    value="{{ old('cost_price', $product->cost_price ?? 0) }}" {{ isset($product) ? 'readonly' : '' }}>
                                <div class="input-group-append"><span class="input-group-text">đ</span></div>
                            </div>
                            @if(isset($product))
                            <small class="text-muted italic">Giá vốn tự động cập nhật từ Phiếu Nhập.</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: CẤU HÌNH HỆ SỐ NHÂN & XEM TRƯỚC GIÁ -->
            <div class="col-md-7">
                <div class="card card-outline card-success shadow">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Chính sách giá & Hệ số nhân</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- HỆ SỐ NỘI BỘ -->
                            <div class="col-md-6 border-right">
                                <label class="text-muted">Hệ số nhân (Factor)</label>
                                <div class="form-group">
                                    <label>Hệ số Lẻ (x)</label>
                                    <input type="number" step="0.01" id="factor_retail" name="factor_retail" class="form-control" value="{{ old('factor_retail', $product->factor_retail ?? 1.5) }}">
                                </div>
                                <div class="form-group">
                                    <label>Hệ số Sỉ (x)</label>
                                    <input type="number" step="0.01" id="factor_wholesale" name="factor_wholesale" class="form-control" value="{{ old('factor_wholesale', $product->factor_wholesale ?? 1.1) }}">
                                </div>
                                <div class="form-group">
                                    <label>Hệ số CTV (x)</label>
                                    <input type="number" step="0.01" id="factor_ctv" name="factor_ctv" class="form-control" value="{{ old('factor_ctv', $product->factor_ctv ?? 1.2) }}">
                                </div>
                            </div>

                            <!-- HỆ SỐ TMĐT -->
                            <div class="col-md-6">
                                <label class="text-muted">Hệ số Sàn TMĐT</label>
                                <div class="form-group">
                                    <label>Lợi nhuận mong muốn (Margin)</label>
                                    <input type="number" step="0.01" id="margin_eco" name="factor_eco_margin" class="form-control" value="{{ old('factor_eco_margin', $product->factor_eco_margin ?? 0.5) }}">
                                    <small class="text-muted">Ví dụ: 0.5 là lãi 50%</small>
                                </div>
                                <div class="form-group">
                                    <label>Phí sàn (%)</label>
                                    <input type="number" step="0.01" id="fee_eco" name="factor_eco_fee" class="form-control" value="{{ old('factor_eco_fee', $product->factor_eco_fee ?? 0.3) }}">
                                    <small class="text-muted">Ví dụ: 0.3 là phí sàn 30%</small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <!-- KHU VỰC XEM TRƯỚC GIÁ -->
                        <label class="mb-3">Xem trước giá bán dự kiến:</label>
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 border rounded bg-light mb-2">
                                    <small class="text-muted">Giá Lẻ</small>
                                    <div class="text-bold text-primary" id="view_retail">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 border rounded bg-light mb-2">
                                    <small class="text-muted">Giá Sỉ</small>
                                    <div class="text-bold text-success" id="view_wholesale">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 border rounded bg-light mb-2">
                                    <small class="text-muted">Giá CTV</small>
                                    <div class="text-bold text-info" id="view_ctv">0</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 border rounded bg-warning mb-2">
                                    <small class="text-dark">Giá Sàn</small>
                                    <div class="text-bold text-dark" id="view_eco">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('products.index') }}" class="btn btn-default px-4">Hủy</a>
                        <button type="submit" class="btn btn-success px-5 font-weight-bold">
                            <i class="fas fa-save"></i> LƯU SẢN PHẨM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function calculateLivePrices() {
        let cost = parseFloat($('#cost_price').val()) || 0;

        // Lấy các hệ số
        let fRetail = parseFloat($('#factor_retail').val()) || 0;
        let fWholesale = parseFloat($('#factor_wholesale').val()) || 0;
        let fCtv = parseFloat($('#factor_ctv').val()) || 0;
        let mEco = parseFloat($('#margin_eco').val()) || 0;
        let fEco = parseFloat($('#fee_eco').val()) || 0;

        // Tính toán
        let priceRetail = cost * fRetail;
        let priceWholesale = cost * fWholesale;
        let priceCtv = cost * fCtv;

        // Công thức sàn: Vốn * (1 + Margin) / (1 - Phí)
        let priceEco = 0;
        if (fEco < 1) {
            priceEco = cost * (1 + mEco) / (1 - fEco);
        }

        // Hiển thị
        const formatter = new Intl.NumberFormat('vi-VN');
        $('#view_retail').text(formatter.format(Math.round(priceRetail)) + 'đ');
        $('#view_wholesale').text(formatter.format(Math.round(priceWholesale)) + 'đ');
        $('#view_ctv').text(formatter.format(Math.round(priceCtv)) + 'đ');
        $('#view_eco').text(formatter.format(Math.round(priceEco)) + 'đ');
    }

    $(document).ready(function() {
        calculateLivePrices();
        // Lắng nghe thay đổi trên tất cả các ô input liên quan đến giá
        $('input[type="number"]').on('input', function() {
            calculateLivePrices();
        });
    });
</script>
@endpush
@endsection