@extends('layouts.app')
@section('title', 'Danh sách Nhà cung cấp')
@section('content')
@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    {{ $errors->first() }}
</div>
@endif
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý Nhà cung cấp</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importSupplierModal">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>

            <a href="{{ route('providers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm NCC mới
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr class="bg-light text-13">
                    <th>Tên nhà cung cấp</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="text-right">Tiền mình nợ NCC</th>
                    <th width="150px">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($suppliers as $s)
                <tr>
                    <td>
                        <a href="{{ route('providers.show', $s->id) }}" class="font-weight-bold">
                            {{ $s->name }}
                        </a>
                    </td>
                    <td>{{ $s->phone }}</td>
                    <td>{{ $s->address }}</td>
                    <td class="text-right text-danger font-weight-bold">
                        {{ number_format($s->total_debt) }} đ
                    </td>
                    <td>
                        <a href="{{ route('providers.show', $s->id) }}" class="btn btn-xs btn-info" title="Xem lịch sử nợ">
                            <i class="fas fa-history"></i>
                        </a>
                        <a href="{{ route('providers.edit', $s->id) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('credit_logs.index') }}?target_type=supplier&target_id={{ $s->id }}" class="btn btn-xs btn-info">
                            <i class="fas fa-book"></i>
                        </a>
                        <form action="{{ route('providers.destroy', $s->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
<div class="modal fade" id="importSupplierModal" tabindex="-1" role="dialog" aria-labelledby="importSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('providers.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="importSupplierModalLabel">Import nhà cung cấp từ Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info">
                    File Excel cần có các cột:
                    <b>Tên nhà cung cấp</b>,
                    <b>Điện thoại</b>,
                    <b>Địa chỉ 1</b>,
                    <b>Tỉnh/Thành phố</b>,
                    <b>Quận huyện</b>,
                    <b>Nợ hiện tại</b>.
                    <br>
                    Nếu trùng số điện thoại, hệ thống sẽ cập nhật NCC cũ.
                    Nếu không có số điện thoại, hệ thống sẽ kiểm tra trùng theo tên NCC.
                </div>

                <div class="form-group">
                    <label>Chọn file Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection