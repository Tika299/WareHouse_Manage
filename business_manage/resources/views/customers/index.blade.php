@extends('layouts.app')
@section('title', 'Quản lý Khách hàng')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Danh sách Khách hàng & Nợ gộp</h3>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm ml-auto">+ Thêm khách hàng</a>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('customers.index') }}" method="GET">
            <div class="row align-items-center">
                <!-- Tìm khách hàng -->
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control"
                        placeholder="Tìm theo tên hoặc số điện thoại..."
                        value="{{ request('search') }}">
                </div>

                <!-- Lọc theo khoảng nợ -->
                <div class="col-md-3 d-flex align-items-center">
                    <span style="white-space: nowrap; margin-right: 10px;">Lọc theo nợ:</span>
                    <input type="number" name="debt_from" class="form-control form-control-sm mr-1"
                        placeholder="Từ"
                        value="{{ request('debt_from') }}">

                    <input type="number" name="debt_to" class="form-control form-control-sm"
                        placeholder="Đến"
                        value="{{ request('debt_to') }}">
                </div>

                <!-- Nút bấm -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-default btn-sm" title="Xóa lọc">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light text-13">
                    <th>Tên khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="text-right">Tổng nợ hiện tại</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($customers as $c)
                <tr>
                    <td><a href="{{ route('customers.show', $c->id) }}" class="font-weight-bold text-primary">{{ $c->name }}</a></td>
                    <td>{{ $c->phone }}</td>
                    <td>{{ $c->address }}</td>
                    <td class="text-right text-danger font-weight-bold">{{ number_format($c->total_debt) }} đ</td>
                    <td class="text-center">
                        <a href="{{ route('customers.show', $c->id) }}" class="btn btn-xs btn-info" title="Đối soát nợ"><i class="fas fa-history"></i></a>
                        <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('credit_logs.index') }}?target_type=customer&target_id={{ $c->id }}" class="btn btn-xs btn-info">
                            <i class="fas fa-book"></i>
                        </a>
                        <form action="{{ route('customers.destroy', $c->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Xóa khách hàng này?')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">{{ $customers->links() }}</div>
</div>
@endsection