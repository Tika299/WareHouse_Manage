@extends('layouts.app')
@section('title', 'Tạo gói Combo mới')
@section('content')
<div class="container-fluid">
    <form action="{{ route('products.store_combo') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Cột trái: Thông tin Combo -->
            <div class="col-md-5">
                <div class="card card-warning card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">1. Thông tin gói Combo</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên gói Combo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Combo Trắng Da Palmer's" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Ngành hàng</label>
                                <select name="category_id" class="form-control select2-tags">
                                    @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Thương hiệu</label>
                                <select name="brand_id" class="form-control select2-tags">
                                    @foreach($brands as $br) <option value="{{ $br->id }}">{{ $br->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group"><label>Mã SKU Combo</label><input type="text" name="sku" class="form-control" placeholder="Tự động nếu để trống"></div>
                            <div class="col-md-6 form-group"><label>Đơn vị tính</label><input type="text" name="unit" class="form-control" value="Bộ"></div>
                        </div>
                        <div class="row bg-light p-2 rounded">
                            <div class="col-md-6 form-group"><label class="small">Giá bán lẻ Combo</label><input type="number" name="manual_retail_price" class="form-control border-primary" placeholder="Bắt buộc"></div>
                            <div class="col-md-6 form-group"><label class="small">Giá vốn Combo (Ước tính)</label><input type="number" name="cost_price" class="form-control" value="0"></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mt-3">
                    <div class="card-header"><h3 class="card-title font-weight-bold small">Mô tả Combo</h3></div>
                    <div class="card-body p-0">
                        <textarea name="description" id="product_desc" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thành phần cấu tạo -->
            <div class="col-md-7">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold">2. Thành phần sản phẩm lẻ</h3>
                        <button type="button" class="btn btn-sm btn-info ml-auto" id="addComboItem"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light text-center small">
                                <tr>
                                    <th>Sản phẩm lẻ / Biến thể con</th>
                                    <th width="120">Số lượng</th>
                                    <th width="40">#</th>
                                </tr>
                            </thead>
                            <tbody id="comboBody">
                                <!-- Dòng trống mặc định -->
                            </tbody>
                        </table>
                        <div id="empty-msg" class="p-4 text-center text-muted">Chưa có sản phẩm nào được chọn.</div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> <b>Ghi chú:</b> Tồn kho của Combo sẽ được hệ thống tính tự động dựa trên sản phẩm có số lượng thấp nhất trong danh sách thành phần này.
                </div>
            </div>

            <div class="col-md-12 text-center mt-3 mb-5">
                <hr>
                <button type="submit" class="btn btn-warning btn-lg px-5 shadow font-weight-bold text-dark"><i class="fas fa-box-open mr-2"></i> TẠO GÓI COMBO</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace('product_desc', { height: 250, language: 'vi', allowedContent: true, versionCheck: false });
        $('.select2-tags').select2({ tags: true });

        let cIdx = 0;
        $('#addComboItem').click(function() {
            $('#empty-msg').hide();
            let html = `<tr>
                <td>
                    <select name="combo_items[${cIdx}][product_id]" class="form-control form-control-sm select2-comp" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        @foreach($components as $lp)
                            <option value="{{ $lp->id }}">{{ $lp->name }} {{ $lp->variant_label ? '('.$lp->variant_label.')' : '' }} - [Tồn: {{ $lp->stock_quantity }}]</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="combo_items[${cIdx}][quantity]" class="form-control form-control-sm text-center" value="1" min="1"></td>
                <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-combo mt-1"><i class="fas fa-trash"></i></button></td>
            </tr>`;
            $('#comboBody').append(html);
            $('.select2-comp').select2();
            cIdx++;
        });

        $(document).on('click', '.remove-combo', function() {
            $(this).closest('tr').remove();
            if ($('#comboBody tr').length == 0) $('#empty-msg').show();
        });
    });
</script>
@endpush