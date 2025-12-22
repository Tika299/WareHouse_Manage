@extends('layouts.app')
@section('title', 'Tạo đơn hàng mới')

@section('content')
<form action="{{ route('exports.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- Cột trái: Chọn sản phẩm -->
        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header"><h3 class="card-title font-weight-bold">Danh sách hàng xuất</h3></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="order-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="100">Tồn</th>
                                <th width="120">Số lượng</th>
                                <th width="150">Đơn giá</th>
                                <th width="150">Thành tiền</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="items[0][product_id]" class="form-control select-product" required>
                                        <option value="">-- Chọn sản phẩm --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->retail_price }}" data-stock="{{ $p->stock_quantity }}">
                                                {{ $p->sku }} - {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center stock-display">-</td>
                                <td><input type="number" name="items[0][quantity]" class="form-control input-qty" value="1" min="1"></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control input-price" value="0"></td>
                                <td class="text-right subtotal-display">0 đ</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-info m-3" id="btn-add-item">+ Thêm sản phẩm</button>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thanh toán & Vận chuyển -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold">Thanh toán & Giao hàng</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Khách hàng</label>
                        <select name="customer_id" class="form-control" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (Nợ cũ: {{ number_format($c->total_debt) }}đ)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Đơn vị vận chuyển</label>
                        <select name="shipping_unit_id" class="form-control">
                            @foreach($shippingUnits as $su)
                                <option value="{{ $su->id }}">{{ $su->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phí vận chuyển (đ)</label>
                        <input type="number" name="shipping_fee" id="shipping_fee" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label>Người chịu phí ship</label>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input ship-payor" type="radio" id="p1" name="shipping_payor" value="customer" checked>
                            <label for="p1" class="custom-control-label">Khách hàng trả (Cộng vào nợ)</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input ship-payor" type="radio" id="p2" name="shipping_payor" value="shop">
                            <label for="p2" class="custom-control-label">Doanh nghiệp trả (Trừ vào lãi)</label>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Tài khoản nhận tiền</label>
                        <select name="account_id" class="form-control">
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} (Dư: {{ number_format($a->current_balance) }}đ)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tiền khách trả trước (đ)</label>
                        <input type="number" name="paid_amount" id="paid_amount" class="form-control" value="0">
                    </div>

                    <!-- Bảng tổng kết -->
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between"><span>Tiền hàng:</span> <b id="sum-product">0 đ</b></div>
                        <div class="d-flex justify-content-between"><span>Phí ship:</span> <b id="sum-ship">0 đ</b></div>
                        <hr>
                        <div class="d-flex justify-content-between text-danger h4 font-weight-bold">
                            <span>TỔNG CỘNG:</span> <span id="sum-final">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between text-primary">
                            <span>Nợ gộp thêm:</span> <b id="sum-debt">0 đ</b>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">XÁC NHẬN XUẤT ĐƠN</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let rowIdx = 1;

    // Thêm dòng sản phẩm mới
    $('#btn-add-item').click(function() {
        let newRow = `
        <tr>
            <td>
                <select name="items[${rowIdx}][product_id]" class="form-control select-product" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-price="{{ $p->retail_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->sku }} - {{ $p->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center stock-display">-</td>
            <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control input-qty" value="1" min="1"></td>
            <td><input type="number" name="items[${rowIdx}][unit_price]" class="form-control input-price" value="0"></td>
            <td class="text-right subtotal-display">0 đ</td>
            <td><button type="button" class="btn btn-danger btn-xs btn-remove">x</button></td>
        </tr>`;
        $('#order-table tbody').append(newRow);
        rowIdx++;
    });

    // Xóa dòng
    $(document).on('click', '.btn-remove', function() {
        $(this).closest('tr').remove();
        calculateAll();
    });

    // Khi chọn sản phẩm: Tự đổ giá và tồn kho
    $(document).on('change', '.select-product', function() {
        let opt = $(this).find(':selected');
        let tr = $(this).closest('tr');
        tr.find('.input-price').val(opt.data('price') || 0);
        tr.find('.stock-display').text(opt.data('stock') || 0);
        calculateAll();
    });

    // Khi thay đổi số lượng, giá, phí ship
    $(document).on('input', '.input-qty, .input-price, #shipping_fee, #paid_amount, .ship-payor', function() {
        calculateAll();
    });

    function calculateAll() {
        let totalProd = 0;
        
        // Duyệt từng dòng hàng
        $('#order-table tbody tr').each(function() {
            let qty = parseFloat($(this).find('.input-qty').val()) || 0;
            let price = parseFloat($(this).find('.input-price').val()) || 0;
            let sub = qty * price;
            $(this).find('.subtotal-display').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');
            totalProd += sub;
        });

        let shipFee = parseFloat($('#shipping_fee').val()) || 0;
        let isCustomerPay = $('input[name="shipping_payor"]:checked').val() === 'customer';
        
        let totalFinal = totalProd + (isCustomerPay ? shipFee : 0);
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let debt = totalFinal - paid;

        // Hiển thị tổng kết
        $('#sum-product').text(new Intl.NumberFormat('vi-VN').format(totalProd) + ' đ');
        $('#sum-ship').text(new Intl.NumberFormat('vi-VN').format(shipFee) + ' đ');
        $('#sum-final').text(new Intl.NumberFormat('vi-VN').format(totalFinal) + ' đ');
        $('#sum-debt').text(new Intl.NumberFormat('vi-VN').format(debt > 0 ? debt : 0) + ' đ');
    }
</script>
@endpush
@endsection