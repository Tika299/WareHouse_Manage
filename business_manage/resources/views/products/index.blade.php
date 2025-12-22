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
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="bg-light text-13">
                <tr>
                    <th>Mã SKU</th>
                    <th>Tên hàng</th>
                    <th>Tồn kho</th>
                    <th>Giá vốn (BQGQ)</th>
                    <th>Mức cộng lẻ</th>
                    <th>Giá lẻ dự kiến</th>
                    <th>Mức cộng sỉ</th>
                    <th>Giá sỉ dự kiến</th>
                    <th width="100px">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($products as $p)
                <tr>
                    <td>{{ $p->sku }}</td>
                    <td>{{ $p->name }}</td>
                    <td class="{{ $p->stock_quantity < $p->min_stock ? 'text-danger font-weight-bold' : '' }}">
                        {{ $p->stock_quantity }} {{ $p->unit }}
                    </td>
                    <td class="text-right">{{ number_format($p->cost_price) }}</td>
                    <td class="text-success text-right">+{{ number_format($p->markup_retail) }}</td>
                    <td class="text-primary font-weight-bold text-right">{{ number_format($p->retail_price) }}</td>
                    <td class="text-success text-right">+{{ number_format($p->markup_wholesale) }}</td>
                    <td class="text-orange font-weight-bold text-right">{{ number_format($p->wholesale_price) }}</td>
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
                    <a href="{{ asset('templates/sample_products.xlsx') }}" class="text-primary font-weight-bold">
                        <i class="fas fa-download"></i> Tải file mẫu tại đây
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