@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header bg-dark"><h3 class="card-title">Phiếu Kiểm kê Kho hàng</h3></div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Tồn hệ thống</th>
                    <th>Thực tế tại kho</th>
                    <th>Chênh lệch</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tủ lạnh Samsung</td>
                    <td><span class="badge badge-info">10</span></td>
                    <td><input type="number" class="form-control" value="9"></td>
                    <td><span class="text-danger">-1</span></td>
                    <td><input class="form-control" placeholder="Lý do lệch..."></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-right">
        <button class="btn btn-dark">Cập nhật lại tồn kho thực tế</button>
    </div>
</div>
@endsection