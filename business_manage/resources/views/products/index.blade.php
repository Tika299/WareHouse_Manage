@extends('layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="card-title font-weight-bold text-uppercase text-primary">
                    <i class="fas fa-boxes mr-2"></i>Danh mục Hàng hóa
                </h3>
            </div>
            <div class="col-auto">
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </a>
                <button type="button" class="btn btn-success btn-sm ml-2 shadow-sm" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="small font-weight-bold">Tìm kiếm</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Tên sản phẩm hoặc mã SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small font-weight-bold">Trạng thái kho</label>
                    <select name="stock_status" class="form-control form-control-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng (An toàn)</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Sắp hết hàng (Cảnh báo)</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Đã hết hàng</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-info btn-sm px-4 shadow-sm">Lọc dữ liệu</button>
                    <a href="{{ route('products.index') }}" class="btn btn-default btn-sm ml-1 border shadow-sm">Làm mới</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-gray-light text-12 text-uppercase">
                    <tr>
                        <th width="320" class="pl-4">Sản phẩm / SKU</th>
                        <th class="text-center">Ngành hàng</th>
                        <th width="80" class="text-center">Tồn</th>
                        <th width="110" class="text-right">Giá Vốn</th>
                        <th width="110" class="text-right text-primary">Giá Lẻ</th>
                        <th width="110" class="text-right text-success">Giá Sỉ</th>
                        <th width="110" class="text-right text-info">Giá CTV</th>
                        <th width="110" class="text-right text-orange">Giá Sàn</th>
                        <th width="120" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-13">
                    @forelse($products as $p)
                    @php
                    $hasVariants = $p->variants->isNotEmpty();
                    @endphp
                    {{-- HÀNG CHÍNH (SẢN PHẨM CHA HOẶC SẢN PHẨM ĐƠN LẺ) --}}
                    <tr class="{{ $hasVariants ? 'bg-parent font-weight-bold' : '' }}">
                        <td class="pl-4">
                            <div class="d-flex align-items-center">
                                @if($hasVariants)
                                <button class="btn btn-xs btn-outline-secondary mr-2 btn-toggle-variants" data-target=".child-of-{{ $p->id }}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                @else
                                <span class="badge badge-info mr-2 shadow-sm"></span>
                                @endif
                                <div>
                                    <span>{{ $p->name }}</span><br>
                                    <small class="text-muted">{{ $p->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light border px-2 py-1">{{ $p->product_type ?? 'Chưa phân loại' }}</span>
                        </td>

                        {{-- LOGIC TỒN KHO --}}
                        <td class="text-center">
                            @if(!$hasVariants)
                            <span class="badge {{ $p->stock_quantity <= $p->min_stock ? 'badge-danger' : 'badge-success' }}">
                                {{ $p->stock_quantity }}
                            </span>
                            @else
                            <span class="badge badge-pill badge-dark" title="Tổng tồn của các biến thể">
                                {{ $p->variants->sum('stock_quantity') }}
                            </span>
                            @endif
                        </td>

                        {{-- LOGIC HIỂN THỊ GIÁ --}}
                        @if(!$hasVariants)
                        <td class="text-right text-muted">{{ number_format($p->cost_price) }}</td>
                        <td class="text-right font-weight-bold text-primary">{{ number_format($p->retail_price) }}</td>
                        <td class="text-right text-success">{{ number_format($p->wholesale_price) }}</td>
                        <td class="text-right text-info">{{ number_format($p->ctv_price) }}</td>
                        <td class="text-right text-orange">{{ number_format($p->ecommerce_price) }}</td>
                        @else
                        <td colspan="5" class="text-center text-muted italic small">
                            <i class="fas fa-layer-group mr-1"></i> Có {{ $p->variants->count() }} biến thể (Dung tích/Màu sắc)
                        </td>
                        @endif

                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-xs btn-warning border shadow-sm" title="Sửa"><i class="fas fa-edit"></i></a>

                                <a href="{{ route('products.create') }}?parent_id={{ $p->id }}" class="btn btn-xs btn-primary" title="Thêm biến thể/phiên bản">
                                    <i class="fas fa-plus-square"></i>
                                </a>

                                <button class="btn btn-xs btn-info border shadow-sm btn-view-desc"
                                    data-full-text="{{ $p->description }}"
                                    data-name="{{ $p->name }}"
                                    title="Mô tả">
                                    <i class="fas fa-file-alt"></i>
                                </button>

                                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa sản phẩm này sẽ xóa toàn bộ biến thể liên quan. Bạn chắc chắn chứ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger border shadow-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- DANH SÁCH BIẾN THỂ CON (Hiện ra ngay bên dưới cha) --}}
                    @if($hasVariants)
                    @foreach($p->variants as $v)
                    <tr class="child-row child-of-{{ $p->id }} bg-white">
                        <td class="pl-5">
                            <div class="d-flex align-items-center">
                                <div class="tree-line mr-2"></div>
                                <div>
                                    <span class="text-primary font-weight-bold">{{ $v->variant_label }}</span><br>
                                    <small class="text-muted">{{ $v->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center small text-muted">{{ $v->product_type }}</td>
                        <td class="text-center">
                            <span class="badge {{ $v->stock_quantity <= $v->min_stock ? 'badge-danger' : 'badge-light border' }}">
                                {{ $v->stock_quantity }}
                            </span>
                        </td>
                        <td class="text-right text-muted small">{{ number_format($v->cost_price) }}</td>
                        <td class="text-right text-primary font-weight-bold">{{ number_format($v->retail_price) }}</td>
                        <td class="text-right text-success small">{{ number_format($v->wholesale_price) }}</td>
                        <td class="text-right text-info small">{{ number_format($v->ctv_price) }}</td>
                        <td class="text-right text-orange font-weight-bold">{{ number_format($v->ecommerce_price) }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('products.edit', $v->id) }}" class="btn btn-xs btn-link text-warning p-0 mx-1"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-xs btn-link text-info p-0 mx-1 btn-view-desc"
                                    {{-- Ưu tiên mô tả con, nếu trống lấy mô tả cha --}}
                                    data-full-text="{{ $v->description ?: $p->description }}"
                                    data-name="{{ $p->name }} - {{ $v->variant_label }}"
                                    title="Mô tả">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                                <form action="{{ route('products.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa biến thể này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-link text-danger p-0 mx-1"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    @empty
                    <tr>
                        <td colspan="9" class="text-center p-5 text-muted">Chưa có sản phẩm nào trong hệ thống.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white border-top">
        <div class="float-right shadow-sm">
            {{ $products->links() }}
        </div>
    </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-file-excel mr-2"></i>Nhập sản phẩm từ Excel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Chọn file dữ liệu (.xlsx hoặc .xls)</label>
                        <input type="file" name="excel_file" class="form-control-file border p-2 rounded" required>
                    </div>
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle mr-1"></i> Tải file mẫu bên dưới, điền dữ liệu và upload lại hệ thống.
                    </div>
                    <a href="{{ route('products.template') }}" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
                        <i class="fas fa-download mr-1"></i> TẢI FILE EXCEL MẪU TẠI ĐÂY
                    </a>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Bắt đầu Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL XEM MÔ TẢ --}}
<div class="modal fade" id="modalViewDescription" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold text-uppercase"><i class="fas fa-info-circle mr-2"></i>Mô tả sản phẩm</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <h5 id="modal-product-name" class="text-primary border-bottom pb-2 mb-3"></h5>
                <div id="modal-full-description" class="text-dark" style="white-space: pre-wrap; line-height: 1.6;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Style cho phân cấp cha con */
    .bg-parent {
        background-color: #f8f9fa !important;
        border-bottom: 2px solid #eee !important;
    }

    .bg-variant {
        background-color: #fff;
    }

    .child-row td {
        border-top: 1px dashed #eee !important;
    }

    .tree-line {
        width: 15px;
        height: 20px;
        border-left: 2px solid #dee2e6;
        border-bottom: 2px solid #dee2e6;
        margin-top: -12px;
        margin-left: 10px;
    }

    /* Font size & Badge */
    .text-12 {
        font-size: 11px;
        font-weight: 700;
        color: #777;
    }

    .text-13 {
        font-size: 13.5px;
    }

    .italic {
        font-style: italic;
    }

    .badge-pill {
        padding-right: 0.6em;
        padding-left: 0.6em;
        border-radius: 10rem;
    }

    /* Table hover */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, .05) !important;
    }

    .bg-gray-light {
        background-color: #f4f6f9;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Mặc định hiện tất cả biến thể
        // Nếu muốn ẩn mặc định thì thêm $('.child-row').hide(); ở đây

        // Nút Đóng/Mở biến thể
        $('.btn-toggle-variants').on('click', function() {
            let target = $(this).data('target');
            let icon = $(this).find('i');
            $(target).fadeToggle(100);
            icon.toggleClass('fa-chevron-down fa-chevron-right');
        });

        // Xử lý Modal Mô tả
        $('.btn-view-desc').on('click', function() {
            // Dùng .attr để lấy dữ liệu thô từ HTML, tránh lỗi cache hoặc sai kiểu dữ liệu
            let fullText = $(this).attr('data-full-text');
            let productName = $(this).attr('data-name');

            console.log("Mô tả đọc được:", fullText); // Dòng này để bạn F12 lên kiểm tra

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