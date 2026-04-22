@extends('layouts.app')
@section('title', 'Thêm sản phẩm')
@section('content')
<div class="container-fluid">
    <form action="{{ route('products.store') }}" method="POST" id="productForm">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">1. Thông tin cơ bản</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên sản phẩm chính <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Ngành hàng</label>
                                <select name="category_id" class="form-control select2-tags">
                                    <option value="">-- Chọn hoặc gõ mới --</option>
                                    @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Thương hiệu</label>
                                <select name="brand_id" class="form-control select2-tags">
                                    <option value="">-- Chọn hoặc gõ mới --</option>
                                    @foreach($brands as $br) <option value="{{ $br->id }}">{{ $br->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Hình thức tạo</label>
                            <select id="is_variable" name="is_variable" class="form-control border-primary text-bold">
                                <option value="0">Sản phẩm đơn lẻ</option>
                                <option value="1">Có nhiều biến thể (Dung tích/Màu...)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card card-success card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold small">Hệ số lãi mặc định</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 form-group"><label class="small">Hệ số Lẻ</label><input type="number" step="0.01" name="factor_retail" class="form-control" value="1.5"></div>
                            <div class="col-6 form-group"><label class="small">Hệ số Sỉ</label><input type="number" step="0.01" name="factor_wholesale" class="form-control" value="1.1"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div id="single_box" class="card card-info card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">2. Kho & Giá (Đơn lẻ)</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group"><label>SKU</label><input type="text" name="sku" class="form-control" placeholder="Tự động"></div>
                            <div class="col-md-4 form-group"><label>Giá vốn</label><input type="number" name="cost_price" class="form-control" value="0" min="0"></div>
                            <div class="col-md-4 form-group"><label>Tồn kho</label><input type="number" name="stock_quantity" class="form-control" value="0" min="0"></div>
                        </div>
                        <div class="row border-top pt-2">
                            <div class="col-md-3 form-group"><label class="small text-primary">Giá Lẻ ấn định</label><input type="number" name="manual_retail_price" class="form-control" placeholder="Để trống tự tính" min="0"></div>
                            <div class="col-md-3 form-group"><label class="small text-success">Giá Sỉ ấn định</label><input type="number" name="manual_wholesale_price" class="form-control" placeholder="Để trống tự tính" min="0"></div>
                            <div class="col-md-3 form-group"><label class="small text-info">Giá CTV ấn định</label><input type="number" name="manual_ctv_price" class="form-control" placeholder="Để trống tự tính" min="0"></div>
                            <div class="col-md-3 form-group"><label class="small text-orange">Giá Sàn ấn định</label><input type="number" name="manual_ecommerce_price" class="form-control" placeholder="Để trống tự tính" min="0"></div>
                        </div>
                    </div>
                </div>

                <div id="variable_box" class="card card-info card-outline shadow-sm" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold">2. Danh sách biến thể</h3>
                        <button type="button" class="btn btn-sm btn-info ml-auto" id="addVariant"><i class="fas fa-plus"></i> Thêm dòng</button>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light text-center small">
                                <tr>
                                    <th>Phân loại</th>
                                    <th>SKU</th>
                                    <th>Vốn</th>
                                    <th>Tồn</th>
                                    <th class="text-primary">G.Lẻ</th>
                                    <th class="text-success">G.Sỉ</th>
                                    <th width="40">#</th>
                                </tr>
                            </thead>
                            <tbody id="variantBody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mt-3">
                    <div class="card-body p-0">
                        <textarea name="description" id="product_desc" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-12 text-center mt-3 mb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow font-weight-bold"><i class="fas fa-save mr-2"></i> LƯU SẢN PHẨM</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style> .cke_contents { min-height: 400px !important; } .cke_notification_warning { display: none !important; } </style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace('product_desc', { height: 400, language: 'vi', allowedContent: true, versionCheck: false });
        $('.select2-tags').select2({ tags: true, placeholder: "Chọn hoặc gõ thêm mới" });

        $('#is_variable').change(function() {
            if ($(this).val() == '1') {
                $('#single_box').hide();
                $('#variable_box').show();
                if ($('#variantBody tr').length == 0) $('#addVariant').click();
            } else {
                $('#single_box').show();
                $('#variable_box').hide();
            }
        });

        let vIdx = 0;
        $('#addVariant').click(function() {
            let html = `<tr>
                <td><input type="text" name="variants[${vIdx}][variant_label]" class="form-control form-control-sm" placeholder="VD: 100ml" required></td>
                <td><input type="text" name="variants[${vIdx}][sku]" class="form-control form-control-sm"></td>
                <td><input type="number" name="variants[${vIdx}][cost_price]" class="form-control form-control-sm" value="0"></td>
                <td><input type="number" name="variants[${vIdx}][stock_quantity]" class="form-control form-control-sm" value="0"></td>
                <td><input type="number" name="variants[${vIdx}][manual_retail_price]" class="form-control form-control-sm" placeholder="G.Lẻ"></td>
                <td><input type="number" name="variants[${vIdx}][manual_wholesale_price]" class="form-control form-control-sm" placeholder="G.Sỉ"></td>
                <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-v mt-1"><i class="fas fa-trash"></i></button></td>
            </tr>`;
            $('#variantBody').append(html);
            vIdx++;
        });
        $(document).on('click', '.remove-v', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endpush