@extends('layouts.app')

@section('title', isset($product) ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới')

@section('content')
<div class="container-fluid">
    <form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST" id="productForm">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="row">
            <!-- CỘT TRÁI: CẤU HÌNH CHUNG & HỆ SỐ -->
            <div class="col-md-4">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">1. Thông tin cơ bản</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tên sản phẩm chính <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ví dụ: Nước Hoa Lattafa" required value="{{ old('name', $product->name ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Ngành hàng</label>
                                <select name="product_type" class="form-control">
                                    <option value="Mỹ phẩm" {{ (isset($product) && $product->product_type == 'Mỹ phẩm') ? 'selected' : '' }}>Mỹ phẩm</option>
                                    <option value="Dụng cụ" {{ (isset($product) && $product->product_type == 'Dụng cụ') ? 'selected' : '' }}>Dụng cụ</option>
                                    <option value="Thực phẩm" {{ (isset($product) && $product->product_type == 'Thực phẩm') ? 'selected' : '' }}>Thực phẩm</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Hình thức tạo</label>
                                <select id="is_variable" name="is_variable" class="form-control border-primary font-weight-bold" {{ isset($product) ? 'disabled' : '' }}>
                                    <option value="0" {{ (isset($product) && !$product->variants->isNotEmpty()) ? 'selected' : '' }}>Sản phẩm đơn lẻ</option>
                                    <option value="1" {{ (isset($product) && $product->variants->isNotEmpty()) ? 'selected' : '' }}>Có nhiều biến thể</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Mô tả / Đặc điểm</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- HỆ SỐ NHÂN LÃI (Dùng làm công thức mặc định) -->
                <div class="card card-success card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">2. Hệ số nhân lãi (%)</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 form-group">
                                <label><small>Hệ số Lẻ (x)</small></label>
                                <input type="number" step="0.01" name="factor_retail" class="form-control factor-input" value="{{ $product->factor_retail ?? 1.50 }}">
                            </div>
                            <div class="col-6 form-group">
                                <label><small>Hệ số Sỉ (x)</small></label>
                                <input type="number" step="0.01" name="factor_wholesale" class="form-control factor-input" value="{{ $product->factor_wholesale ?? 1.10 }}">
                            </div>
                            <div class="col-6 form-group">
                                <label><small>Hệ số CTV (x)</small></label>
                                <input type="number" step="0.01" name="factor_ctv" class="form-control factor-input" value="{{ $product->factor_ctv ?? 1.20 }}">
                            </div>
                            <div class="col-6 form-group">
                                <label><small>Lãi Sàn (%)</small></label>
                                <input type="number" step="0.01" name="factor_eco_margin" id="margin_eco" class="form-control factor-input" value="{{ $product->factor_eco_margin ?? 0.50 }}">
                            </div>
                            <div class="col-12 form-group">
                                <label><small>Phí sàn thu (%)</small></label>
                                <input type="number" step="0.01" name="factor_eco_fee" id="fee_eco" class="form-control factor-input" value="{{ $product->factor_eco_fee ?? 0.30 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: CHI TIẾT KHO & GIÁ -->
            <div class="col-md-8">
                <!-- BOX CHO SẢN PHẨM ĐƠN LẺ -->
                <div id="single_box" class="card card-info card-outline shadow-sm">
                    <div class="card-header"><h3 class="card-title font-weight-bold">3. Thông số kho & Giá bán</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Mã SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ $product->sku ?? '' }}" placeholder="Để trống tự tạo">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Giá vốn (đ)</label>
                                <input type="number" name="cost_price" id="cost_price" class="form-control single-calc" value="{{ $product->cost_price ?? 0 }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Tồn ban đầu</label>
                                <input type="number" name="stock_quantity" class="form-control" value="{{ $product->stock_quantity ?? 0 }}">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12 mb-2"><label class="text-primary"><i class="fas fa-edit"></i> Giá ấn định thủ công (Bỏ qua công thức nếu nhập)</label></div>
                            <div class="col-md-3 form-group"><small>Giá Lẻ</small><input type="number" name="manual_retail_price" class="form-control single-calc border-primary" value="{{ $product->manual_retail_price ?? '' }}"></div>
                            <div class="col-md-3 form-group"><small>Giá Sỉ</small><input type="number" name="manual_wholesale_price" class="form-control single-calc border-primary" value="{{ $product->manual_wholesale_price ?? '' }}"></div>
                            <div class="col-md-3 form-group"><small>Giá CTV</small><input type="number" name="manual_ctv_price" class="form-control single-calc border-primary" value="{{ $product->manual_ctv_price ?? '' }}"></div>
                            <div class="col-md-3 form-group"><small>Giá Sàn</small><input type="number" name="manual_ecommerce_price" class="form-control single-calc border-primary" value="{{ $product->manual_ecommerce_price ?? '' }}"></div>
                        </div>
                        
                        <!-- PREVIEW CHO SINGLE -->
                        <div class="row mt-3 p-3 bg-dark rounded mx-0">
                            <div class="col-3 text-center border-right"><small class="text-gray">Lẻ chốt</small><div class="text-bold" id="view_retail">0đ</div></div>
                            <div class="col-3 text-center border-right"><small class="text-gray">Sỉ chốt</small><div class="text-bold" id="view_wholesale">0đ</div></div>
                            <div class="col-3 text-center border-right"><small class="text-gray">CTV chốt</small><div class="text-bold" id="view_ctv">0đ</div></div>
                            <div class="col-3 text-center"><small class="text-gray">Sàn chốt</small><div class="text-bold" id="view_eco">0đ</div></div>
                        </div>
                    </div>
                </div>

                <!-- BOX CHO SẢN PHẨM BIẾN THỂ -->
                <div id="variable_box" class="card card-info card-outline shadow-sm" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h3 class="card-title font-weight-bold">3. Danh sách biến thể</h3>
                        <button type="button" class="btn btn-sm btn-info ml-auto" id="addVariant"><i class="fas fa-plus"></i> Thêm dòng</button>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="min-width: 1100px;">
                            <thead class="bg-light text-center text-12">
                                <tr>
                                    <th width="150">Dung tích/Màu</th>
                                    <th width="120">SKU</th>
                                    <th width="100">Vốn</th>
                                    <th width="80">Tồn</th>
                                    <th class="bg-primary-light">Giá Lẻ (đ)</th>
                                    <th class="bg-primary-light">Giá Sỉ (đ)</th>
                                    <th class="bg-primary-light">Giá CTV (đ)</th>
                                    <th class="bg-primary-light">Giá Sàn (đ)</th>
                                    <th width="40">#</th>
                                </tr>
                            </thead>
                            <tbody id="variantBody">
                                {{-- Load bằng JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12 text-center mt-4 mb-5">
                <hr>
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-lg font-weight-bold">
                    <i class="fas fa-save mr-2"></i> LƯU SẢN PHẨM
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .text-12 { font-size: 11.5px; text-transform: uppercase; }
    .bg-dark { background-color: #1e1e2d !important; }
    .text-gray { color: #a1a1c3; }
    .bg-primary-light { background-color: #f0f7ff; }
    .manual-variant-input { border: 1px solid #007bff !important; text-align: right; font-weight: bold; }
</style>

@push('scripts')
<script>
    let vIdx = 0;

    $(document).ready(function() {
        // 1. Chuyển đổi giao diện
        function toggleUI() {
            if ($('#is_variable').val() === '1') {
                $('#single_box').hide();
                $('#variable_box').show();
                if ($('#variantBody tr').length === 0) $('#addVariant').click();
            } else {
                $('#single_box').show();
                $('#variable_box').hide();
            }
        }
        $('#is_variable').change(toggleUI);
        toggleUI(); // Chạy khi load trang (cho edit)

        // 2. Thêm dòng biến thể
        $('#addVariant').click(function() {
            let html = `
                <tr>
                    <td><input type="text" name="variants[${vIdx}][variant_label]" class="form-control form-control-sm" placeholder="VD: 500ml" required></td>
                    <td><input type="text" name="variants[${vIdx}][sku]" class="form-control form-control-sm" placeholder="Tự động"></td>
                    <td><input type="number" name="variants[${vIdx}][cost_price]" class="form-control form-control-sm" value="0"></td>
                    <td><input type="number" name="variants[${vIdx}][stock_quantity]" class="form-control form-control-sm" value="0"></td>
                    <td><input type="number" name="variants[${vIdx}][manual_retail_price]" class="form-control form-control-sm manual-variant-input" placeholder="Auto"></td>
                    <td><input type="number" name="variants[${vIdx}][manual_wholesale_price]" class="form-control form-control-sm manual-variant-input" placeholder="Auto"></td>
                    <td><input type="number" name="variants[${vIdx}][manual_ctv_price]" class="form-control form-control-sm manual-variant-input" placeholder="Auto"></td>
                    <td><input type="number" name="variants[${vIdx}][manual_ecommerce_price]" class="form-control form-control-sm manual-variant-input" placeholder="Auto"></td>
                    <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-v mt-1"><i class="fas fa-trash"></i></button></td>
                </tr>`;
            $('#variantBody').append(html);
            vIdx++;
        });

        $(document).on('click', '.remove-v', function() { $(this).closest('tr').remove(); });

        // 3. Logic Preview cho đơn lẻ
        function calculateSinglePreview() {
            let cost = parseFloat($('#cost_price').val()) || 0;
            let fRetail = parseFloat($('input[name="factor_retail"]').val()) || 0;
            let fWholesale = parseFloat($('input[name="factor_wholesale"]').val()) || 0;
            let fCtv = parseFloat($('input[name="factor_ctv"]').val()) || 0;
            let mEco = parseFloat($('#margin_eco').val()) || 0;
            let fEco = parseFloat($('#fee_eco').val()) || 0;

            const updateField = (name, factor, displayId) => {
                let manual = $(`input[name="manual_${name}_price"]`).val();
                let result = (manual > 0) ? manual : (cost * factor);
                
                // Riêng Ecommerce có công thức đặc thù
                if(name === 'ecommerce' && (!manual || manual <= 0)) {
                    result = (fEco < 1) ? (cost * (1 + mEco) / (1 - fEco)) : 0;
                }

                $(displayId).text(new Intl.NumberFormat('vi-VN').format(Math.round(result)) + 'đ');
                if(manual > 0) $(displayId).addClass('text-warning').removeClass('text-white');
                else $(displayId).addClass('text-white').removeClass('text-warning');
            };

            updateField('retail', fRetail, '#view_retail');
            updateField('wholesale', fWholesale, '#view_wholesale');
            updateField('ctv', fCtv, '#view_ctv');
            updateField('ecommerce', 0, '#view_eco');
        }

        $(document).on('input', '.single-calc, .factor-input', calculateSinglePreview);
        calculateSinglePreview();
    });
</script>
@endpush
@endsection