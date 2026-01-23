@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh mục Hàng hóa & Giá vốn</h3>
        <div class="card-tools">
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">+ Thêm sản phẩm</a>
            <button type="button" class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#importModal">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
        </div>
    </div>

    <!-- BỘ LỌC VÀ TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="row">
                <div class="col-md-5">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm theo tên sản phẩm hoặc mã SKU..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="stock_status" class="form-control">
                        <option value="">-- Tất cả trạng thái kho --</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>Còn hàng (An toàn)</option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Sắp hết hàng (Cảnh báo)</option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Đã hết hàng</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-info px-4">Lọc dữ liệu</button>
                    <a href="{{ route('products.index') }}" class="btn btn-default">Xóa bộ lọc</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="bg-light text-13">
                <tr>
                    <th>Mã SKU</th>
                    <th>Tên hàng</th>
                    <th>Tồn kho</th>
                    <th>Giá Vốn</th>
                    <th>Giá Lẻ</th>
                    <th>Giá Sỉ</th>
                    <th>Giá CTV</th>
                    <th>Giá Sàn</th>
                    <th width="100px">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($products as $p)
                <tr>
                    <td class="bg-light"><b>{{ $p->sku }}</b></td>
                    <td>{{ $p->name }}</td>
                    <td class="{{ $p->stock_quantity < $p->min_stock ? 'text-danger font-weight-bold' : '' }}">
                        {{ $p->stock_quantity }} {{ $p->unit }}
                    </td>
                    <td class="text-right text-bold text-danger">{{ number_format($p->cost_price) }}</td>

                    <!-- Hiển thị giá và hệ số trong ngoặc -->
                    <td class="text-right">{{ number_format($p->retail_price) }} <br><small>(x{{ $p->factor_retail }})</small></td>
                    <td class="text-right">{{ number_format($p->wholesale_price) }} <br><small>(x{{ $p->factor_wholesale }})</small></td>
                    <td class="text-right">{{ number_format($p->ctv_price) }} <br><small>(x{{ $p->factor_ctv }})</small></td>

                    <!-- Giá sàn nổi bật -->
                    <td class="text-right text-orange text-bold">
                        {{ number_format($p->ecommerce_price) }}
                        <br><small>(M:{{ $p->factor_eco_margin }} | F:{{ $p->factor_eco_fee }})</small>
                    </td>
                    <td>
                        <a href="{{ route('products.edit', $p->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('products.destroy', $p->id) }}" method="POST" style="display:inline-block">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Xóa sản phẩm này?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-footer clearfix">
            {{-- Phân trang --}}
            <div class="">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nhập sản phẩm từ Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Chọn file (.xlsx hoặc .xls)</label>
                        <input type="file" name="excel_file" class="form-control-file" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <b>Yêu cầu định dạng file:</b><br>
                            - Hàng đầu tiên là tiêu đề: ma_sku, ten_san_pham, don_vi_tinh, gia_von, muc_cong_le, muc_cong_si, ton_kho, ton_toi_thieu.
                        </small>
                    </div>
                    <a href="{{ route('products.template') }}" class="btn btn-outline-primary btn-sm btn-block">
                        <i class="fas fa-download"></i> Tải file Excel mẫu tại đây
                    </a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Bắt đầu Import</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection