@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Danh sách Nhân viên</h3></div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò (Role)</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nguyễn Văn Kế Toán</td>
                    <td>ketoan@erp.com</td>
                    <td><span class="badge badge-danger">Kế toán</span></td>
                    <td><span class="badge badge-success">Đang làm việc</span></td>
                    <td><button class="btn btn-xs btn-default">Phân quyền</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection