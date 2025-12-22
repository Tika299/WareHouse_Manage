@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">iPhone 15 Pro Max</h3>
                <p class="text-muted text-center">SKU: IP15PM</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item"><b>Tồn kho hiện tại</b> <a class="float-right text-bold">12 cái</a></li>
                    <li class="list-group-item"><b>Giá vốn (BQGQ)</b> <a class="float-right text-primary">28,500,000đ</a></li>
                    <li class="list-group-item"><b>Giá sỉ (+2tr)</b> <a class="float-right">30,500,000đ</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#stock-log" data-toggle="tab">Thẻ kho</a></li>
                    <li class="nav-item"><a class="nav-link" href="#price-history" data-toggle="tab">Lịch sử giá vốn</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="active tab-pane" id="stock-log">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Loại phiếu</th>
                                    <th>Số lượng</th>
                                    <th>Tồn cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>21/12 10:00</td>
                                    <td>Nhập hàng #PN001</td>
                                    <td class="text-success">+10</td>
                                    <td>12</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection