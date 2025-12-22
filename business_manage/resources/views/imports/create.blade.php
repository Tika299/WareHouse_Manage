@extends('layouts.app')
@section('content')
<form action="{{ route('imports.store') }}" method="POST" id="importForm">
    @csrf
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Lập Phiếu Nhập Hàng</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Nhà cung cấp</label>
                    <select name="supplier_id" class="form-control" required>
                        @foreach($suppliers as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Chi từ tài khoản</label>
                    <select name="account_id" class="form-control">
                        @foreach($accounts as $a) <option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->current_balance) }})</option> @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Phí phát sinh (Ship/Thuế)</label>
                    <input type="number" name="extra_cost" id="extra_cost" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label>Thanh toán ngay</label>
                    <input type="number" name="paid_amount" class="form-control" value="0">
                </div>
            </div>

            <table class="table table-bordered mt-4" id="productTable">
                <thead>
                    <tr class="bg-light">
                        <th>Sản phẩm</th>
                        <th width="150">Số lượng</th>
                        <th width="200">Giá nhập NCC</th>
                        <th width="200">Thành tiền</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-control">
                                @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }} (Tồn: {{ $p->stock_quantity }})</option> @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control qty" value="1"></td>
                        <td><input type="number" name="items[0][import_price]" class="form-control price" value="0"></td>
                        <td class="text-right subtotal">0 đ</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-info" id="addRow">+ Thêm sản phẩm</button>
        </div>
        <div class="card-footer">
            <input type="hidden" name="total_product_value" id="total_product_value" value="0">
            <h4 class="text-right">Tổng tiền hàng: <span id="display_total">0</span> đ</h4>
            <button type="submit" class="btn btn-primary float-right">Xác nhận Nhập kho</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let rowIdx = 1;
    $('#addRow').click(function() {
        let html = `<tr>
            <td><select name="items[${rowIdx}][product_id]" class="form-control">@foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach</select></td>
            <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control qty" value="1"></td>
            <td><input type="number" name="items[${rowIdx}][import_price]" class="form-control price" value="0"></td>
            <td class="text-right subtotal">0 đ</td>
            <td><button type="button" class="btn btn-danger btn-xs removeRow">x</button></td>
        </tr>`;
        $('#productTable tbody').append(html);
        rowIdx++;
    });

    $(document).on('click', '.removeRow', function() { $(this).closest('tr').remove(); calculateTotal(); });
    $(document).on('input', '.qty, .price', function() { calculateTotal(); });

    function calculateTotal() {
        let grandTotal = 0;
        $('#productTable tbody tr').each(function() {
            let q = parseFloat($(this).find('.qty').val()) || 0;
            let p = parseFloat($(this).find('.price').val()) || 0;
            let sub = q * p;
            $(this).find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');
            grandTotal += sub;
        });
        $('#total_product_value').val(grandTotal);
        $('#display_total').text(new Intl.NumberFormat('vi-VN').format(grandTotal));
    }
</script>
@endpush
@endsection