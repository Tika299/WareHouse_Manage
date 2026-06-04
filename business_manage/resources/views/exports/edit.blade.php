@extends('layouts.app')
@section('title', 'Chỉnh sửa đơn hàng')

@section('content')
@if(session('msg'))
    <div class="alert alert-success">
        {{ session('msg') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('exports.update', $order->id) }}" method="POST" id="exportForm">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Cột trái: Sản phẩm -->
        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold">
                        Sản phẩm xuất kho
                        <small class="text-muted ml-2">#DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</small>
                    </h3>

                    <div style="width: 350px;" class="ml-auto">
                        <div class="input-group" style="flex-wrap: nowrap;">
                            <x-select2-ajax
                                name="product_search"
                                id="product_search"
                                label=""
                                :url="route('products.searchAjax')"
                                placeholder="Gõ tên hoặc mã SKU để thêm..." />

                            <button type="button" class="btn btn-success ml-1" data-toggle="modal" data-target="#quickAddProductModal" title="Thêm nhanh sản phẩm mới">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="order-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="80" class="text-center">Tồn khả dụng</th>
                                <th width="100" class="text-center">Số lượng</th>
                                <th width="150" class="text-center">Đơn giá</th>
                                <th width="150" class="text-center">Thành tiền</th>
                                <th width="40"></th>
                            </tr>
                        </thead>

                        <tbody id="order-body">
                            <tr id="empty-row" style="{{ $order->details->count() ? 'display:none;' : '' }}">
                                <td colspan="6" class="text-center p-4 text-muted">
                                    Chưa có sản phẩm nào. Hãy tìm ở ô phía trên.
                                </td>
                            </tr>

                            @foreach($order->details as $index => $detail)
                                @php
                                    $product = $detail->product;
                                    $availableStock = ($product->stock_quantity ?? 0) + $detail->quantity;
                                    $displayName = $product->name ?? 'Sản phẩm không tồn tại';
                                    if (!empty($product->variant_label)) {
                                        $displayName .= ' - ' . $product->variant_label;
                                    }
                                @endphp

                                <tr id="row-{{ $detail->product_id }}">
                                    <td>
                                        <b class="text-primary">{{ $product->sku ?? '' }}</b>
                                        @if(!empty($product->sku)) - @endif
                                        {{ $displayName }}

                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $detail->product_id }}">
                                    </td>

                                    <td class="text-center text-bold">
                                        {{ $availableStock }}
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="items[{{ $index }}][quantity]"
                                            class="form-control form-control-sm qty text-center"
                                            value="{{ old('items.' . $index . '.quantity', $detail->quantity) }}"
                                            min="1"
                                            max="{{ $availableStock }}"
                                            required>
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="items[{{ $index }}][unit_price]"
                                            class="form-control form-control-sm price text-right"
                                            value="{{ old('items.' . $index . '.unit_price', $detail->unit_price) }}"
                                            min="0"
                                            required>
                                    </td>

                                    <td class="text-right subtotal font-weight-bold text-danger">0 đ</td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
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
                        <div class="input-group">
                            <div class="flex-grow-1">
                                <x-select2-ajax
                                    name="customer_id"
                                    id="customer_id"
                                    label=""
                                    :url="route('customers.searchAjax')"
                                    placeholder="-- Tìm khách hàng --"
                                    required="true"
                                    :value="$order->customer_id"
                                    :text="$order->customer ? ($order->customer->name . ' - ' . $order->customer->phone) : null" />
                            </div>

                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddCustomer">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Đơn vị vận chuyển</label>
                        <div class="input-group">
                            <select name="shipping_unit_id" id="shipping_unit_id" class="form-control form-control-sm" required>
                                @foreach($shippingUnits as $su)
                                    <option value="{{ $su->id }}" {{ $order->shipping_unit_id == $su->id ? 'selected' : '' }}>
                                        {{ $su->name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="input-group-append">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddShipping">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Phí ship</label>
                                <input type="number"
                                    name="shipping_fee"
                                    id="shipping_fee"
                                    class="form-control form-control-sm"
                                    value="{{ old('shipping_fee', $order->shipping_fee ?? 0) }}"
                                    min="0">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label>Người trả ship</label>
                                <select name="shipping_payor" id="shipping_payor" class="form-control form-control-sm">
                                    <option value="customer" {{ old('shipping_payor', $order->shipping_payor ?? 'customer') == 'customer' ? 'selected' : '' }}>
                                        Khách trả
                                    </option>
                                    <option value="shop" {{ old('shipping_payor', $order->shipping_payor ?? 'customer') == 'shop' ? 'selected' : '' }}>
                                        Shop trả
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tài khoản nhận tiền</label>
                        <div class="input-group">
                            <select name="account_id" id="account_id" class="form-control form-control-sm" required>
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}" {{ old('account_id', $order->account_id) == $a->id ? 'selected' : '' }}>
                                        {{ $a->name }} ({{ number_format($a->current_balance) }}đ)
                                    </option>
                                @endforeach
                            </select>

                            <div class="input-group-append">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddAccount">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tiền khách trả trước</label>
                        <input type="number"
                            name="paid_amount"
                            id="paid_amount"
                            class="form-control form-control-lg text-success"
                            value="{{ old('paid_amount', $order->paid_amount ?? 0) }}"
                            min="0">
                    </div>

                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between">
                            <span>Tiền hàng:</span>
                            <b id="sum-product">0 đ</b>
                        </div>

                        <div class="d-flex justify-content-between" id="ship-row">
                            <span>Phí ship:</span>
                            <b id="sum-ship">0 đ</b>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between text-danger h4 font-weight-bold">
                            <span>TỔNG CỘNG:</span>
                            <span id="sum-final">0 đ</span>
                        </div>

                        <div class="d-flex justify-content-between text-primary">
                            <span>Khách còn nợ:</span>
                            <b id="sum-debt">0 đ</b>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <input type="hidden" name="total_product_amount" id="total_product_amount" value="{{ $order->total_product_amount ?? 0 }}">
                    <input type="hidden" name="total_final_amount" id="total_final_amount" value="{{ $order->total_final_amount ?? 0 }}">

                    <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
                        CẬP NHẬT ĐƠN HÀNG
                    </button>

                    <a href="{{ route('exports.show', $order->id) }}" class="btn btn-default btn-block mt-2">
                        Quay lại chi tiết đơn
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL THÊM TÀI KHOẢN NHANH --}}
<div class="modal fade" id="modalAddAccount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-wallet"></i> Thêm Tài Khoản Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>

            <form id="quickAddAccountForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên tài khoản/Ngân hàng <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Techcombank, Tiền mặt..." required>
                    </div>

                    <div class="form-group">
                        <label>Loại</label>
                        <select name="type" class="form-control">
                            <option value="cash">Tiền mặt</option>
                            <option value="bank">Ngân hàng</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Số dư ban đầu</label>
                        <input type="number" name="initial_balance" class="form-control" value="0" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btnSaveAccount">Lưu tài khoản</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM KHÁCH HÀNG -->
<div class="modal fade" id="modalAddCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm Khách Hàng Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form id="formQuickAddCustomer">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên khách hàng *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-block">Lưu và Chọn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM ĐƠN VỊ VẬN CHUYỂN -->
<div class="modal fade" id="modalAddShipping" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-truck"></i> Thêm Đơn Vị Vận Chuyển</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form id="formQuickAddShipping">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên đơn vị *</label>
                        <input type="text" name="name" class="form-control" required placeholder="GHTK, Viettel Post...">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-block">Lưu đơn vị</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM NHANH SẢN PHẨM -->
<x-modal-quick-add-product />

@push('scripts')
<script>
    let rowIdx = {{ $order->details->count() }};

    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(value || 0) + ' đ';
    }

    function addProductToTable(data) {
        if (!data || !data.id) return;

        if ($(`#row-${data.id}`).length > 0) {
            Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
            return;
        }

        let stock = parseInt(data.stock || data.stock_quantity || 0);

        if (stock <= 0) {
            Swal.fire('Cảnh báo', 'Sản phẩm này đã hết hàng!', 'error');
            return;
        }

        $('#empty-row').hide();

        let displayName = data.name || '';
        if (data.variant_label) {
            displayName += ` - <span class="badge badge-secondary">${data.variant_label}</span>`;
        }

        let sku = data.sku || '';
        let price = data.retail_price || data.price || 0;

        let html = `
            <tr id="row-${data.id}">
                <td>
                    <b class="text-primary">${sku}</b>${sku ? ' - ' : ''}${displayName}
                    <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                </td>
                <td class="text-center text-bold">${stock}</td>
                <td>
                    <input type="number" name="items[${rowIdx}][quantity]" class="form-control form-control-sm qty text-center" value="1" min="1" max="${stock}" required>
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm price text-right" value="${price}" min="0" required>
                </td>
                <td class="text-right subtotal font-weight-bold text-danger">0 đ</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;

        $('#order-body').append(html);
        rowIdx++;
        calculateAll();
    }

    function calculateAll() {
        let totalProd = 0;

        $('#order-body tr').each(function() {
            if ($(this).attr('id') === 'empty-row') return;

            let q = parseFloat($(this).find('.qty').val()) || 0;
            let p = parseFloat($(this).find('.price').val()) || 0;
            let sub = q * p;

            $(this).find('.subtotal').text(formatMoney(sub));
            totalProd += sub;
        });

        let shipFee = parseFloat($('#shipping_fee').val()) || 0;
        let payor = $('#shipping_payor').val();
        let totalFinal = totalProd + (payor === 'customer' ? shipFee : 0);
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let debt = totalFinal - paid;

        $('#sum-product').text(formatMoney(totalProd));
        $('#sum-ship').text(formatMoney(shipFee));
        $('#sum-final').text(formatMoney(totalFinal));
        $('#sum-debt').text(formatMoney(debt > 0 ? debt : 0));

        $('#total_product_amount').val(totalProd);
        $('#total_final_amount').val(totalFinal);
    }

    $(document).ready(function() {
        calculateAll();

        $('#product_search').on('select2:select', function(e) {
            addProductToTable(e.params.data);
            $(this).val(null).trigger('change');
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();

            if ($('#order-body tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }

            calculateAll();
        });

        $(document).on('input change', '.qty, .price, #shipping_fee, #paid_amount, #shipping_payor', function() {
            calculateAll();
        });

        $('#exportForm').on('submit', function(e) {
            if ($('#order-body tr:visible').not('#empty-row').length === 0) {
                e.preventDefault();
                Swal.fire('Thiếu sản phẩm', 'Vui lòng thêm ít nhất 1 sản phẩm vào đơn hàng.', 'warning');
                return false;
            }
        });

        $('#quickAddAccountForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('accounts.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    let formatted = new Intl.NumberFormat('vi-VN').format(res.data.current_balance);
                    let newOpt = new Option(res.data.name + " (" + formatted + "đ)", res.data.id, true, true);
                    $('#account_id').append(newOpt).trigger('change');
                    $('#modalAddAccount').modal('hide');
                    $('#quickAddAccountForm')[0].reset();
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Không thể thêm tài khoản.', 'error');
                }
            });
        });

        $('#formQuickAddCustomer').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    let newOpt = new Option(res.data.name + ' - ' + res.data.phone, res.data.id, true, true);
                    $('#customer_id').append(newOpt).trigger('change');
                    $('#modalAddCustomer').modal('hide');
                    $('#formQuickAddCustomer')[0].reset();
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Không thể thêm khách hàng.', 'error');
                }
            });
        });

        $('#formQuickAddShipping').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('shipping_units.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#shipping_unit_id').append(`<option value="${res.data.id}" selected>${res.data.name}</option>`);
                    $('#modalAddShipping').modal('hide');
                    $('#formQuickAddShipping')[0].reset();
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', xhr.responseJSON?.message || 'Không thể thêm đơn vị vận chuyển.', 'error');
                }
            });
        });
    });
</script>
@endpush
@endsection
