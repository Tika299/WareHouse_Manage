@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST">
            @csrf
            @if(isset($product)) @method('PUT') @endif
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($product) ? 'Chỉnh sửa sản phẩm' : 'Thêm mới sản phẩm' }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Mã SKU (Barcode)</label>
                            <input type="text" name="sku" class="form-control" value="{{ $product->sku ?? '' }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" value="{{ $product->name ?? '' }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Giá vốn hiện tại (Đọc từ kho)</label>
                            <input type="number" id="cost_price" name="cost_price" class="form-control bg-light" value="{{ $product->cost_price ?? 0 }}" readonly>
                            <small class="text-muted">Giá vốn tự động tính từ Phiếu Nhập.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mức cộng Lẻ (Nhập tay)</label>
                            <input type="number" id="markup_retail" name="markup_retail" class="form-control" value="{{ $product->markup_retail ?? 0 }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mức cộng Sỉ (Nhập tay)</label>
                            <input type="number" id="markup_wholesale" name="markup_wholesale" class="form-control" value="{{ $product->markup_wholesale ?? 0 }}">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text text-primary">GIÁ BÁN LẺ DỰ KIẾN</span>
                                    <span class="info-box-number h3" id="preview_retail">0 đ</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light border">
                                <div class="info-box-content">
                                    <span class="info-box-text text-orange">GIÁ BÁN SỈ DỰ KIẾN</span>
                                    <span class="info-box-number h3" id="preview_wholesale">0 đ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('products.index') }}" class="btn btn-default">Hủy</a>
                    <button type="submit" class="btn btn-info">Lưu sản phẩm</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function calculatePrices() {
        let cost = parseFloat($('#cost_price').val()) || 0;
        let markupRetail = parseFloat($('#markup_retail').val()) || 0;
        let markupWholesale = parseFloat($('#markup_wholesale').val()) || 0;

        let retail = cost + markupRetail;
        let wholesale = cost + markupWholesale;

        $('#preview_retail').text(new Intl.NumberFormat('vi-VN').format(retail) + ' đ');
        $('#preview_wholesale').text(new Intl.NumberFormat('vi-VN').format(wholesale) + ' đ');
    }

    $(document).ready(function() {
        calculatePrices();
        $('#markup_retail, #markup_wholesale').on('input', function() {
            calculatePrices();
        });
    });
</script>
@endpush
@endsection