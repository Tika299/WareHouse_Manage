@extends('layouts.app')
@section('title', 'Sửa: ' . $product->name)
@section('content')
<div class="container-fluid">
    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="convert_to_single" id="convert_to_single" value="0">

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline card-tabs shadow-sm">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#info">Thông tin & Kho</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#pricing">Hệ số giá</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="info">
                                <div class="row">
                                    <div class="col-md-4 form-group"><label>Tên SP</label><input type="text" name="name" class="form-control" value="{{ $product->name }}" required></div>
                                    <div class="col-md-3 form-group"><label>Thương hiệu</label>
                                        <select name="brand_id" class="form-control select2-tags">
                                            @foreach($brands as $br) <option value="{{ $br->id }}" {{ $product->brand_id == $br->id ? 'selected' : '' }}>{{ $br->name }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group"><label>Ngành hàng</label>
                                        <select name="category_id" class="form-control select2-tags">
                                            @foreach($categories as $cat) <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 form-group"><label>Đơn vị</label><input type="text" name="unit" class="form-control" value="{{ $product->unit }}"></div>
                                </div>

                                <div id="single_product_fields" style="{{ $product->variants->isNotEmpty() ? 'display:none;' : '' }}">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3 form-group"><label>SKU</label><input type="text" name="sku" class="form-control font-weight-bold" value="{{ $product->sku }}"></div>
                                        <div class="col-md-3 form-group"><label>Giá vốn</label><input type="number" name="cost_price" class="form-control" value="{{ (int)$product->cost_price }}"></div>
                                        <div class="col-md-3 form-group"><label>Tồn kho</label><input type="number" name="stock_quantity" class="form-control bg-light" value="{{ $product->stock_quantity }}"></div>
                                        <div class="col-md-3 form-group"><label>Tồn tối thiểu</label><input type="number" name="min_stock" class="form-control" value="{{ $product->min_stock }}"></div>
                                    </div>
                                    <div class="row bg-light p-2 rounded mb-3">
                                        <div class="col-md-3 form-group"><label class="small text-primary">Giá Lẻ ấn định</label><input type="number" name="manual_retail_price" class="form-control form-control-sm" value="{{ (int)$product->manual_retail_price ?: '' }}"></div>
                                        <div class="col-md-3 form-group"><label class="small text-success">Giá Sỉ ấn định</label><input type="number" name="manual_wholesale_price" class="form-control form-control-sm" value="{{ (int)$product->manual_wholesale_price ?: '' }}"></div>
                                        <div class="col-md-3 form-group"><label class="small text-info">Giá CTV ấn định</label><input type="number" name="manual_ctv_price" class="form-control form-control-sm" value="{{ (int)$product->manual_ctv_price ?: '' }}"></div>
                                        <div class="col-md-3 form-group"><label class="small text-orange">Giá Sàn ấn định</label><input type="number" name="manual_ecommerce_price" class="form-control form-control-sm" value="{{ (int)$product->manual_ecommerce_price ?: '' }}"></div>
                                    </div>
                                    @if($product->variants->isEmpty())<div class="text-center"><button type="button" class="btn btn-outline-warning btn-sm" id="btnConvertToVariable"><i class="fas fa-exchange-alt"></i> Chuyển sang Biến thể</button></div>@endif
                                </div>

                                <div id="variant_management_section" style="{{ $product->variants->isNotEmpty() ? '' : 'display:none;' }}">
                                    <hr><h6 class="text-primary font-weight-bold">PHẦN BIẾN THỂ</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="bg-light small text-center">
                                                <tr>
                                                    <th>Phân loại</th>
                                                    <th>SKU</th>
                                                    <th>Vốn</th>
                                                    <th>Tồn</th>
                                                    <th class="text-primary">G.Lẻ</th>
                                                    <th class="text-success">G.Sỉ</th>
                                                    <th class="text-info">G.CTV</th>
                                                    <th class="text-orange">G.Sàn</th>
                                                    <th>#</th>
                                                </tr>
                                            </thead>
                                            <tbody id="variantBody">
                                                @foreach($product->variants as $v)
                                                <tr>
                                                    <td><input type="text" name="variants[{{ $v->id }}][variant_label]" class="form-control form-control-sm v-label" value="{{ $v->variant_label }}" required></td>
                                                    <td><input type="text" name="variants[{{ $v->id }}][sku]" class="form-control form-control-sm v-sku" value="{{ $v->sku }}" required></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][cost_price]" class="form-control form-control-sm v-cost" value="{{ (int)$v->cost_price }}"></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][stock_quantity]" class="form-control form-control-sm v-stock" value="{{ $v->stock_quantity }}"></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][manual_retail_price]" class="form-control form-control-sm" value="{{ (int)$v->manual_retail_price ?: '' }}"></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][manual_wholesale_price]" class="form-control form-control-sm" value="{{ (int)$v->manual_wholesale_price ?: '' }}"></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][manual_ctv_price]" class="form-control form-control-sm" value="{{ (int)$v->manual_ctv_price ?: '' }}"></td>
                                                    <td><input type="number" name="variants[{{ $v->id }}][manual_ecommerce_price]" class="form-control form-control-sm" value="{{ (int)$v->manual_ecommerce_price ?: '' }}"></td>
                                                    <td class="text-center"><button type="button" class="btn btn-xs btn-outline-danger btn-collapse" title="Về đơn lẻ"><i class="fas fa-compress-alt"></i></button></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm mt-2" id="addVariant"><i class="fas fa-plus"></i> Thêm mới</button>
                                </div>
                                <div class="mt-4"><textarea name="description" id="product_desc">{!! $product->description !!}</textarea></div>
                            </div>
                            
                            <div class="tab-pane fade" id="pricing">
                                <div class="row">
                                    <div class="col-md-4 form-group"><label>Hệ số Lẻ (x)</label><input type="number" step="0.01" name="factor_retail" class="form-control" value="{{ $product->factor_retail }}"></div>
                                    <div class="col-md-4 form-group"><label>Hệ số Sỉ (x)</label><input type="number" step="0.01" name="factor_wholesale" class="form-control" value="{{ $product->factor_wholesale }}"></div>
                                    <div class="col-md-4 form-group"><label>Hệ số CTV (x)</label><input type="number" step="0.01" name="factor_ctv" class="form-control" value="{{ $product->factor_ctv }}"></div>
                                    <div class="col-md-6 form-group"><label>Lãi Sàn mong muốn (%)</label><input type="number" step="0.01" name="factor_eco_margin" class="form-control" value="{{ $product->factor_eco_margin }}"></div>
                                    <div class="col-md-6 form-group"><label>Phí sàn thu (%)</label><input type="number" step="0.01" name="factor_eco_fee" class="form-control" value="{{ $product->factor_eco_fee }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right bg-white border-top">
                        <a href="{{ route('products.index') }}" class="btn btn-default">Quay lại</a>
                        <button type="submit" class="btn btn-primary px-5 shadow">LƯU CẬP NHẬT</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        CKEDITOR.replace('product_desc', { height: 500, language: 'vi', allowedContent: true, versionCheck: false });
        $('.select2-tags').select2({ tags: true, placeholder: "Chọn hoặc thêm" });

        $('#btnConvertToVariable').click(function() {
            if(confirm('Chuyển đổi?')) {
                let sku = $('input[name="sku"]').val();
                $('input[name="sku"]').val(sku + '-P');
                $('#single_product_fields').hide(); $('#variant_management_section').show();
                addNewVariantRow("Mặc định", sku, $('input[name="cost_price"]').val(), $('input[name="stock_quantity"]').val());
            }
        });

        $(document).on('click', '.btn-collapse', function() {
            if(confirm('Thu gọn về đơn lẻ?')) {
                let row = $(this).closest('tr');
                $('input[name="sku"]').val(row.find('.v-sku').val());
                $('input[name="cost_price"]').val(row.find('.v-cost').val());
                $('input[name="stock_quantity"]').val(row.find('.v-stock').val());
                $('#convert_to_single').val('1');
                $('#single_product_fields').show(); $('#variant_management_section').hide();
            }
        });

        let newIdx = 999;
        function addNewVariantRow(label='', sku='', cost=0, stock=0) {
            let html = `<tr class="table-warning">
                <td><input type="text" name="new_variants[${newIdx}][variant_label]" class="form-control form-control-sm" value="${label}" required></td>
                <td><input type="text" name="new_variants[${newIdx}][sku]" class="form-control form-control-sm" value="${sku}"></td>
                <td><input type="number" name="new_variants[${newIdx}][cost_price]" class="form-control form-control-sm" value="${cost}"></td>
                <td><input type="number" name="new_variants[${newIdx}][stock_quantity]" class="form-control form-control-sm" value="${stock}"></td>
                <td><input type="number" name="new_variants[${newIdx}][manual_retail_price]" class="form-control form-control-sm" placeholder="G.Lẻ"></td>
                <td><input type="number" name="new_variants[${newIdx}][manual_wholesale_price]" class="form-control form-control-sm" placeholder="G.Sỉ"></td>
                <td class="text-center"><button type="button" class="btn btn-xs btn-danger remove-new-v"><i class="fas fa-trash"></i></button></td>
            </tr>`;
            $('#variantBody').append(html); newIdx++;
        }
        $('#addVariant').click(function() { addNewVariantRow(); });
        $(document).on('click', '.remove-new-v', function() { $(this).closest('tr').remove(); });
    });
</script>
@endpush