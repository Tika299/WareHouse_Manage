@extends('layouts.app')
@section('title', 'Tạo đơn hàng mới')

@section('content')
<form action="{{ route('exports.store') }}" method="POST" id="exportForm">
    @csrf
    <div class="row">
        <!-- Cột trái: Sản phẩm -->
        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold">Sản phẩm xuất kho</h3>
                    <!-- Ô TÌM KIẾM NẰM RIÊNG ĐỂ TỐI ƯU -->
                    <div style="width: 300px;" class="ml-auto">
                        <x-select2-ajax
                            name="product_search"
                            id="product_search"
                            label=""
                            :url="route('products.searchAjax')"
                            placeholder="Gõ tên hoặc mã SKU để thêm..." />
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="order-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="80" class="text-center">Tồn</th>
                                <th width="100" class="text-center">Số lượng</th>
                                <th width="150" class="text-center">Đơn giá</th>
                                <th width="150" class="text-center">Thành tiền</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="order-body">
                            <tr id="empty-row">
                                <td colspan="6" class="text-center p-4 text-muted">Chưa có sản phẩm nào. Hãy tìm ở ô phía trên.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thanh toán & Vận chuyển -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Thanh toán & Vận chuyển</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Khách hàng <span class="text-danger">*</span></label>
                        <x-select2-ajax
                            name="customer_id"
                            id="customer_id"
                            label=""
                            :url="route('customers.searchAjax')"
                            placeholder="-- Tìm khách hàng --"
                            required="true" />
                    </div>

                    <div class="form-group">
                        <label>Đơn vị vận chuyển</label>
                        <select name="shipping_unit_id" class="form-control form-control-sm">
                            @foreach($shippingUnits as $su)
                            <option value="{{ $su->id }}">{{ $su->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Phí ship</label>
                                <input type="number" name="shipping_fee" id="shipping_fee" class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Người trả ship</label>
                                <select name="shipping_payor" id="shipping_payor" class="form-control form-control-sm">
                                    <option value="customer">Khách trả</option>
                                    <option value="shop">Shop trả</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tài khoản nhận tiền</label>
                        <select name="account_id" class="form-control form-control-sm">
                            @foreach($accounts as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->current_balance) }}đ)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tiền khách trả trước</label>
                        <input type="number" name="paid_amount" id="paid_amount" class="form-control form-control-lg text-success" value="0">
                    </div>

                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between"><span>Tiền hàng:</span> <b id="sum-product">0 đ</b></div>
                        <div class="d-flex justify-content-between" id="ship-row"><span>Phí ship:</span> <b id="sum-ship">0 đ</b></div>
                        <hr>
                        <div class="d-flex justify-content-between text-danger h4 font-weight-bold">
                            <span>TỔNG CỘNG:</span> <span id="sum-final">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between text-primary">
                            <span>Khách còn nợ:</span> <b id="sum-debt">0 đ</b>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="total_product_amount" id="total_product_amount" value="0">
                    <input type="hidden" name="total_final_amount" id="total_final_amount" value="0">
                    <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">XÁC NHẬN XUẤT ĐƠN</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let rowIdx = 0;

    $(document).ready(function() {
        // Sự kiện khi chọn sản phẩm từ ô tìm kiếm AJAX
        $('#product_search').on('select2:select', function(e) {
            let data = e.params.data;

            // 1. Kiểm tra trùng
            if ($(`#row-${data.id}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
                $(this).val(null).trigger('change');
                return;
            }

            // 2. Ẩn dòng trống
            $('#empty-row').hide();

            // 3. Thêm dòng mới vào table
            let html = `
                <tr id="row-${data.id}">
                    <td>
                        <b class="text-primary">${data.sku}</b> - ${data.name}
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                    </td>
                    <td class="text-center">${data.stock}</td>
                    <td>
                        <input type="number" name="items[${rowIdx}][quantity]" class="form-control form-control-sm qty text-center" value="1" min="1">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm price text-right" value="${data.retail_price}">
                    </td>
                    <td class="text-right subtotal font-weight-bold">0 đ</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;

            $('#order-body').append(html);
            rowIdx++;

            // 4. Reset ô tìm kiếm
            $(this).val(null).trigger('change');
            calculateAll();
        });

        // Xóa dòng
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#order-body tr').length === 0 || ($('#order-body tr').length === 1 && $('#order-body tr').attr('id') === 'empty-row')) {
                $('#empty-row').show();
            }
            calculateAll();
        });

        // Tính toán khi thay đổi Input
        $(document).on('input', '.qty, .price, #shipping_fee, #paid_amount, #shipping_payor', function() {
            calculateAll();
        });

        function calculateAll() {
            let totalProd = 0;
            $('#order-body tr').each(function() {
                if ($(this).attr('id') === 'empty-row') return;
                let q = parseFloat($(this).find('.qty').val()) || 0;
                let p = parseFloat($(this).find('.price').val()) || 0;
                let sub = q * p;
                $(this).find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');
                totalProd += sub;
            });

            let shipFee = parseFloat($('#shipping_fee').val()) || 0;
            let payor = $('#shipping_payor').val();

            // Nếu khách trả ship thì cộng vào tổng đơn, nếu shop trả thì không
            let totalFinal = totalProd + (payor === 'customer' ? shipFee : 0);
            let paid = parseFloat($('#paid_amount').val()) || 0;
            let debt = totalFinal - paid;

            $('#sum-product').text(new Intl.NumberFormat('vi-VN').format(totalProd) + ' đ');
            $('#sum-ship').text(new Intl.NumberFormat('vi-VN').format(shipFee) + ' đ');
            $('#sum-final').text(new Intl.NumberFormat('vi-VN').format(totalFinal) + ' đ');
            $('#sum-debt').text(new Intl.NumberFormat('vi-VN').format(debt > 0 ? debt : 0) + ' đ');

            $('#total_product_amount').val(totalProd);
            $('#total_final_amount').val(totalFinal);
        }
    });
</script>
@endpush
@endsection