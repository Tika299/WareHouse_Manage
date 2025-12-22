@extends('layouts.app')
@section('content')
<div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title">Danh sách Phiếu tiếp nhận</h3></div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Khách hàng</th>
                    <th>Tên máy/Sản phẩm</th>
                    <th>Tình trạng nhận</th>
                    <th>Ngày hẹn trả</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#TN001</td>
                    <td>Trần Văn B</td>
                    <td>Laptop Dell XPS 13</td>
                    <td>Mất nguồn, trầy xước nhẹ</td>
                    <td>25/12/2025</td>
                    <td><button class="btn btn-xs btn-primary">Xử lý</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection