@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title">Quản lý Nhóm đối tượng</h3>
        <button class="btn btn-primary btn-sm ml-auto">+ Thêm nhóm mới</button>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Mã nhóm</th>
                    <th>Tên nhóm</th>
                    <th>Loại</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>VIP</td>
                    <td>Khách hàng thân thiết</td>
                    <td><span class="badge badge-info">Khách hàng</span></td>
                    <td>Nhóm khách chiết khấu 5%</td>
                    <td><button class="btn btn-xs btn-warning">Sửa</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection