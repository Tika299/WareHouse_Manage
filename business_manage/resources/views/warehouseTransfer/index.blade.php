@extends('layouts.app')
@section('content')
<div class="card card-warning card-outline">
    <div class="card-header"><h3 class="card-title">Điều chuyển kho nội bộ</h3></div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Từ kho</label>
                <select class="form-control"><option>Kho Tổng</option></select>
            </div>
            <div class="col-md-4">
                <label>Đến kho</label>
                <select class="form-control"><option>Cửa hàng Quận 1</option></select>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng chuyển</th>
                    <th>Lý do</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ốp lưng iPhone 15</td>
                    <td><input type="number" class="form-control" value="50"></td>
                    <td><input type="text" class="form-control" placeholder="Nhập lý do..."></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-right"><button class="btn btn-warning">Xác nhận chuyển kho</button></div>
</div>
@endsection