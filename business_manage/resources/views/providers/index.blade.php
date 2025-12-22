@extends('layouts.app')
@section('title', 'Danh sách Nhà cung cấp')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quản lý Nhà cung cấp</h3>
        <div class="card-tools">
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
</div>
@endsection