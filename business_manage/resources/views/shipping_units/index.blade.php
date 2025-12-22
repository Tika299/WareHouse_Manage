@extends('layouts.app')
@section('title', 'Đơn vị vận chuyển')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Danh sách Đơn vị vận chuyển</h3>
        <a href="{{ route('shipping_units.create') }}" class="btn btn-primary btn-sm ml-auto">+ Thêm đơn vị</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light text-13">
                    <th>Tên đơn vị</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Số đơn đã giao</th>
                    <th width="120px" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @foreach($units as $unit)
                <tr>
                    <td class="font-weight-bold">{{ $unit->name }}</td>
                    <td>{{ $unit->phone }}</td>
                    <td>{{ $unit->address }}</td>
                    <td class="text-center">{{ $unit->salesOrders->count() }}</td>
                    <td class="text-center">
                        <a href="{{ route('shipping_units.edit', $unit->id) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('shipping_units.destroy', $unit->id) }}" method="POST" class="d-inline">
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