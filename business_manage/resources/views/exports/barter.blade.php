@extends('layouts.app')
@section('title', 'Nghiệp vụ Đổi hàng')
@section('content')
<form action="{{ route('exports.storeBarter') }}" method="POST">
    @csrf
    <div class="row">
        <!-- BÊN TRÁI: HÀNG MÌNH XUẤT ĐI -->
        <div class="col-md-6">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">1. Hàng mình xuất đi</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered" id="export-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="100">SL</th>
                                <th width="120">Giá bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="export_items[0][product_id]" class="form-control select-export">
                                        @foreach($products as $p) <option value="{{$p->id}}" data-price="{{$p->retail_price}}">{{$p->name}}</option> @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="export_items[0][quantity]" class="form-control exp-qty" value="1"></td>
                                <td><input type="number" name="export_items[0][unit_price]" class="form-control exp-price" value="0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BÊN PHẢI: HÀNG KHÁCH ĐƯA TỚI -->
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">2. Hàng khách đưa (Thu mua)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered" id="import-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm thu</th>
                                <th width="100">SL</th>
                                <th width="120">Giá thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="import_items[0][product_id]" class="form-control">
                                        @foreach($products as $p) <option value="{{$p->id}}">{{$p->name}}</option> @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="import_items[0][quantity]" class="form-control imp-qty" value="1"></td>
                                <td><input type="number" name="import_items[0][buyback_price]" class="form-control imp-price" value="0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TỔNG KẾT BÙ TRỪ -->
    <div class="card shadow">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <label>Tổng hàng xuất</label>
                    <h3 id="total-exp">0 đ</h3>
                </div>
                <div class="col-md-1"><i class="fas fa-minus mt-4"></i></div>
                <div class="col-md-3">
                    <label>Tổng hàng thu</label>
                    <h3 id="total-imp">0 đ</h3>
                </div>
                <div class="col-md-1"><i class="fas fa-equals mt-4"></i></div>
                <div class="col-md-4 bg-light p-2 rounded border">
                    <label>CHÊNH LỆCH (KHÁCH BÙ)</label>
                    <h2 class="text-danger font-weight-bold" id="diff-value">0 đ</h2>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <label>Chọn khách hàng</label>
                    <select name="customer_id" class="form-control">
                        @foreach($customers as $c) <option value="{{$c->id}}">{{$c->name}} (Nợ: {{number_format($c->total_debt)}})</option> @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Khách đưa thêm tiền mặt (nếu có)</label>
                    <input type="number" name="paid_amount" id="paid_amount" class="form-control" value="0">
                </div>
                <div class="col-md-4">
                    <label>Tài khoản nhận tiền chênh lệch <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-control" required>
                        <option value="">-- Chọn tài khoản --</option>
                        @foreach($accounts as $a)
                        <option value="{{ $a->id }}">{{ $a->name }} (Dư: {{ number_format($a->current_balance) }}đ)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Đơn vị vận chuyển <span class="text-danger">*</span></label>
                    <select name="shipping_unit_id" class="form-control" required>
                        <option value="">-- Chọn ĐVVC --</option>
                        @foreach($shippingUnits as $su)
                        <option value="{{ $su->id }}">{{ $su->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-warning btn-block font-weight-bold">XÁC NHẬN ĐỔI HÀNG</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(document).on('input', '.exp-qty, .exp-price, .imp-qty, .imp-price, #paid_amount', function() {
        calculateBarter();
    });

    function calculateBarter() {
        let totalExp = 0;
        $('.exp-qty').each(function(i, obj) {
            totalExp += ($(obj).val() * $('.exp-price').eq(i).val());
        });

        let totalImp = 0;
        $('.imp-qty').each(function(i, obj) {
            totalImp += ($(obj).val() * $('.imp-price').eq(i).val());
        });

        $('#total-exp').text(new Intl.NumberFormat('vi-VN').format(totalExp) + ' đ');
        $('#total-imp').text(new Intl.NumberFormat('vi-VN').format(totalImp) + ' đ');
        $('#diff-value').text(new Intl.NumberFormat('vi-VN').format(totalExp - totalImp) + ' đ');
    }
</script>
@endpush
@endsection