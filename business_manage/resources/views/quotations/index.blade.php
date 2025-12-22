@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Quản lý Phiếu báo giá</h3></div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Mã báo giá</th>
                    <th>Khách hàng</th>
                    <th>Tổng giá trị</th>
                    <th>Ngày tạo</th>
                    <th>Hiệu lực đến</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#BG2025-001</td>
                    <td>Công ty xây dựng ABC</td>
                    <td>50,000,000đ</td>
                    <td>22/12/2025</td>
                    <td>30/12/2025</td>
                    <td>
                        <button class="btn btn-xs btn-success">Duyệt -> Xuất đơn</button>
                        <button class="btn btn-xs btn-default">In PDF</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection