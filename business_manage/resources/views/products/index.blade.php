@extends('layouts.app')
@section('title', 'Quản lý Sản phẩm')
@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-2">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold text-primary"><i class="fas fa-boxes mr-2"></i>QUẢN LÝ SẢN PHẨM</h3>
            <div>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-plus"></i> Thêm mới</a>
                <button class="btn btn-success btn-sm ml-1 shadow-sm" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-excel"></i> Import</button>
                <a href="{{route('products.create_combo')}}" class="btn btn-info btn-sm ml-1 shadow-sm"><i class="fas fa-box-open"></i> Tạo Combo</a>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light py-2">
        <form action="{{ route('products.index') }}" method="GET" class="row gx-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm tên hoặc SKU..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Ngành hàng --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="brand_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Thương hiệu --</option>
                    @foreach($brands as $br)
                    <option value="{{ $br->id }}" {{ request('brand_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">-- Kho hàng --</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Sắp hết</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-info btn-sm px-3">Lọc</button>
                <a href="{{ route('products.index') }}" class="btn btn-default btn-sm border">Làm mới</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr class="bg-gray-light text-muted text-12 text-uppercase">
                        <th class="pl-3" style="width: 25%">Sản phẩm / SKU</th>
                        <th class="text-center">Ngành / Hiệu</th>
                        <th class="text-center">Tồn</th>
                        <th class="text-right text-primary">Giá Lẻ</th>
                        <th class="text-right text-success">Giá Sỉ</th>
                        <th class="text-right text-orange">Giá Sàn</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-13">
                    @forelse($products as $p)
                    @php $isVar = $p->variants->count() > 0; @endphp
                    <tr class="{{ $isVar ? 'bg-parent-row' : '' }}">
                        <td class="pl-3">
                            <div class="d-flex align-items-center">
                                @if($isVar)
                                <span class="btn-toggle-row mr-2" data-target=".child-of-{{ $p->id }}" style="cursor:pointer"><i class="fas fa-angle-double-right text-primary"></i></span>
                                <span class="badge badge-secondary mr-2">CHA</span>
                                @else
                                <span class="mr-2" style="width: 15px; display: inline-block;"></span>
                                <span class="badge badge-info mr-2">ĐƠN</span>
                                @endif
                                <div>
                                    @if($p->brand)
                                    <small class="text-primary font-weight-bold uppercase" style="font-size: 9px; display: block; margin-bottom: -3px;">
                                        {{ $p->brand->name }}
                                    </small>
                                    @endif
                                    <span class="font-weight-bold {{ $isVar ? 'text-primary' : 'text-dark' }}">{{ $p->name }}</span><br>
                                    <small class="text-muted extra-small">SKU: {{ $p->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light border">{{ $p->category->name }}</span>
                        </td>
                        <td class="text-center font-weight-bold">
                            @if($isVar) {{ $p->variants->sum('stock_quantity') }}
                            @else
                            <span class="badge {{ $p->stock_quantity <= 0 ? 'badge-danger' : ($p->stock_quantity <= $p->min_stock ? 'badge-warning' : 'badge-success') }}">
                                {{ $p->stock_quantity }}
                            </span>
                            @endif
                        </td>
                        @if(!$isVar)
                        <td class="text-right text-primary font-weight-bold">{{ number_format($p->retail_price) }}</td>
                        <td class="text-right text-success">{{ number_format($p->wholesale_price) }}</td>
                        <td class="text-right text-orange">{{ number_format($p->ecommerce_price) }}</td>
                        @else
                        <td colspan="3" class="text-center text-muted small italic">Có {{ $p->variants->count() }} phiên bản</td>
                        @endif
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-xs btn-default border"><i class="fas fa-edit text-warning"></i></a>
                                <button class="btn btn-xs btn-default border btn-view-desc" data-name="{{ $p->name }}"><i class="fas fa-file-alt text-success"></i><textarea class="d-none raw-description">{!! $p->description !!}</textarea></button>
                                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa?')">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-default border"><i class="fas fa-trash text-danger"></i></button></form>
                            </div>
                        </td>
                    </tr>
                    @if($isVar)
                    @foreach($p->variants as $v)
                    <tr class="child-row child-of-{{ $p->id }} bg-white" style="display: none;">
                        <td class="pl-5"><span class="text-dark font-weight-bold">{{ $v->variant_label }}</span>
                            <div class="text-muted extra-small">SKU: {{ $v->sku }}</div>
                        </td>
                        <td class="text-center small text-muted">---</td>
                        <td class="text-center"><span class="badge {{ $v->stock_quantity <= $v->min_stock ? 'badge-warning' : 'badge-light border' }}">{{ $v->stock_quantity }}</span></td>
                        <td class="text-right text-primary">{{ number_format($v->retail_price) }}</td>
                        <td class="text-right text-success">{{ number_format($v->wholesale_price) }}</td>
                        <td class="text-right text-orange">{{ number_format($v->ecommerce_price) }}</td>
                        <td class="text-center">
                            <form action="{{ route('products.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa biến thể này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-danger border-0 bg-transparent mx-1"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="7" class="text-center p-4">Không tìm thấy sản phẩm.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top py-2">{{ $products->links() }}</div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-excel mr-2"></i>Nhập sản phẩm từ Excel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Chọn file dữ liệu (.xlsx hoặc .xls)</label>
                        <input type="file" name="excel_file" class="form-control-file border p-2 rounded w-100" required>
                    </div>
                    <a href="{{ route('products.template') }}" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
                        <i class="fas fa-download mr-1"></i> TẢI FILE EXCEL MẪU
                    </a>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm">Bắt đầu Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL XEM MÔ TẢ (JS GIẢI MÃ CHUẨN) -->
<div class="modal fade" id="modalViewDescription" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title font-weight-bold small">CHI TIẾT MÔ TẢ</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <h5 id="modal-product-name" class="text-primary border-bottom pb-2 mb-3 font-weight-bold"></h5>
                <div id="modal-full-description" class="rendered-html" style="max-height: 500px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-parent-row {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .child-row td {
        border-top: 1px dashed #eee !important;
        vertical-align: middle !important;
    }

    .tree-branch {
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 50%;
        width: 15px;
        border-left: 2px solid #dee2e6;
        border-bottom: 2px solid #dee2e6;
    }

    .text-12 {
        font-size: 11px;
    }

    .text-13 {
        font-size: 13.5px;
    }

    .extra-small {
        font-size: 10px;
    }

    .btn-toggle-row {
        transition: transform 0.2s;
        display: inline-block;
    }

    .btn-toggle-row.active {
        transform: rotate(90deg);
    }

    /* Lớp bao bọc nội dung mô tả */
    .rendered-html {
        line-height: 1.8;
        color: #333;
        font-family: 'Segoe UI', Roboto, sans-serif;
        font-size: 15px;
        padding: 10px 15px;
    }

    /* Định dạng tiêu đề H3 - Tạo ngăn cách rõ ràng */
    .rendered-html h3 {
        font-size: 17px;
        font-weight: 700;
        color: #222;
        text-transform: uppercase;
        margin-top: 25px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #eee;
        /* Đường gạch chân ngăn cách */
        display: block;
    }

    /* Định dạng các đoạn văn */
    .rendered-html p {
        margin-bottom: 12px;
        display: block;
        /* Ép xuống dòng */
    }

    /* Định dạng danh sách có dấu chấm */
    .rendered-html ul {
        margin-bottom: 15px;
        padding-left: 20px;
        list-style-type: disc !important;
        /* Hiện dấu chấm tròn */
    }

    .rendered-html li {
        margin-bottom: 8px;
        line-height: 1.6;
    }

    /* Làm nổi bật chữ đậm */
    .rendered-html strong {
        color: #000;
        font-weight: 600;
    }

    /* Phần hashtag cuối bài */
    .rendered-html p:last-child {
        margin-top: 20px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        color: #0056b3;
    }

    /* Tùy chỉnh thanh cuộn modal */
    #modal-full-description::-webkit-scrollbar {
        width: 6px;
    }

    #modal-full-description::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle ẩn/hiện biến thể
        $('.btn-toggle-row').on('click', function() {
            let target = $(this).data('target');
            $(target).toggle();
            $(this).toggleClass('active');
        });

        // Xử lý hiển thị mô tả
        $('.btn-view-desc').on('click', function() {
            // Dùng .attr để lấy dữ liệu thô từ HTML, tránh lỗi cache hoặc sai kiểu dữ liệu
            let fullText = $(this).find('.raw-description').val() || $(this).find('.raw-description').text() || '';
            let productName = $(this).attr('data-name');

            // Kiểm tra chính xác: nếu là undefined, null hoặc chuỗi chỉ có khoảng trắng
            if (fullText === undefined || fullText === null || fullText.trim() === "") {
                Swal.fire({
                    icon: 'info',
                    title: 'Thông báo',
                    text: 'Sản phẩm này chưa có mô tả chi tiết.',
                    confirmButtonText: 'Đóng'
                });
                return;
            }

            // Đổ dữ liệu vào Modal
            $('#modal-product-name').text(productName);

            // Chuyển các dấu xuống dòng thành thẻ <br> để hiển thị đẹp trong HTML
            let formattedText = fullText.replace(/\n/g, '<br>');
            $('#modal-full-description').html(formattedText);

            // Hiện Modal
            $('#modalViewDescription').modal('show');
        });
    });
</script>
@endpush