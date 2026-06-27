@extends('layouts.app')
@section('title', 'Tạo phiếu hoàn trả NCC')

@section('content')
<form action="{{ route('purchase-returns.store') }}" method="POST" id="purchaseReturnForm">
    @csrf

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-danger shadow">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-undo-alt"></i> Danh sách sản phẩm hoàn trả
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light text-13">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="100" class="text-center">Đã nhập</th>
                                <th width="100" class="text-center">Đã trả</th>
                                <th width="100" class="text-center">Còn trả</th>
                                <th width="140" class="text-center">SL trả</th>
                                <th width="150" class="text-right">Đơn giá nhập</th>
                                <th width="160" class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="return-body">
                            <tr id="empty-row">
                                <td colspan="7" class="text-center p-5 text-muted">
                                    Vui lòng chọn phiếu nhập gốc bên phải để bắt đầu hoàn trả.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-danger shadow">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Thông tin phiếu nhập gốc</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Chọn phiếu nhập <span class="text-danger">*</span></label>
                        <x-select2-ajax
                            name="purchase_order_id"
                            id="purchase_order_id"
                            :url="route('purchase-returns.searchOrdersAjax')"
                            placeholder="Nhập mã phiếu (#PN00001) hoặc tên NCC..."
                            required="true"
                            :value="$selectedOrder->id ?? null"
                            :text="$selectedOrder ? ('#PN' . str_pad($selectedOrder->id, 5, '0', STR_PAD_LEFT) . ' - ' . ($selectedOrder->supplier->name ?? 'Không rõ NCC')) : null" />
                    </div>

                    <div id="supplier-info" class="alert alert-info py-2" style="display:none">
                        Nhà cung cấp: <b id="supplier-name-display">-</b>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú / lý do hoàn trả</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Hàng lỗi, giao nhầm, hàng vỡ..."></textarea>
                    </div>

                    <div class="bg-dark p-3 rounded text-center">
                        <small class="text-muted text-uppercase">Tổng giá trị hoàn trả</small>
                        <h2 class="text-warning font-weight-bold mb-0" id="total-display">0 đ</h2>
                    </div>

                    <button type="submit" class="btn btn-danger btn-block btn-lg mt-3 font-weight-bold" id="confirmPurchaseReturnBtn">
                        <i class="fas fa-check-circle"></i> XÁC NHẬN HOÀN TRẢ
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let allowSubmitPurchaseReturn = false;

    function formatMoney(value) {
        return new Intl.NumberFormat('vi-VN').format(value || 0) + ' đ';
    }

    function calculateTotal() {
        let total = 0;

        $('#return-body tr').each(function() {
            if ($(this).attr('id') === 'empty-row') return;

            const qty = parseFloat($(this).find('.qty').val()) || 0;
            const price = parseFloat($(this).find('.price').val()) || 0;
            const available = parseFloat($(this).find('.available').text()) || 0;

            if (qty > available) {
                $(this).find('.qty').val(available);
            }

            const finalQty = parseFloat($(this).find('.qty').val()) || 0;
            const subtotal = finalQty * price;

            $(this).find('.subtotal').text(formatMoney(subtotal));
            total += subtotal;
        });

        $('#total-display').text(formatMoney(total));
    }

    function renderDetails(order, details) {
        $('#supplier-info').show();
        $('#supplier-name-display').text(order.supplier_name);

        $('#return-body').empty();

        if (!details || details.length === 0) {
            $('#return-body').html('<tr id="empty-row"><td colspan="7" class="text-center p-4 text-muted">Phiếu nhập này chưa có sản phẩm để hoàn trả.</td></tr>');
            $('#total-display').text('0 đ');
            return;
        }

        details.forEach(function(item, index) {
            const row = `
                <tr>
                    <td>
                        <b class="text-primary">${item.sku}</b> - ${item.product_name}
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                    </td>
                    <td class="text-center">${item.purchased_qty}</td>
                    <td class="text-center">${item.returned_qty}</td>
                    <td class="text-center available">${item.available_qty}</td>
                    <td>
                        <input type="number"
                               name="items[${index}][quantity]"
                               class="form-control qty text-center"
                               value="0"
                               min="0"
                               max="${item.available_qty}">
                    </td>
                    <td>
                        <input type="number"
                               name="items[${index}][return_price]"
                               class="form-control price text-right"
                               value="${item.return_price}"
                               readonly>
                    </td>
                    <td class="text-right subtotal">0 đ</td>
                </tr>
            `;
            $('#return-body').append(row);
        });

        calculateTotal();
    }

    function loadOrderDetails(orderId) {
        if (!orderId) return;

        $('#return-body').html('<tr id="empty-row"><td colspan="7" class="text-center p-4 text-muted">Đang tải sản phẩm...</td></tr>');

        $.ajax({
            url: "{{ url('finance/purchase-returns/get-order-details') }}/" + orderId,
            method: "GET",
            success: function(response) {
                if (!response || !response.order) {
                    Swal.fire('Lỗi', 'Không tìm thấy phiếu nhập gốc.', 'error');
                    return;
                }

                renderDetails(response.order, response.details || []);
            },
            error: function() {
                Swal.fire('Lỗi', 'Không thể tải chi tiết phiếu nhập.', 'error');
            }
        });
    }

    $(document).ready(function() {
        $('#purchaseReturnForm').on('submit', function(e) {
            if (allowSubmitPurchaseReturn) {
                return true;
            }

            e.preventDefault();
            $('#confirmPurchaseReturnModal').modal('show');
        });

        $('#confirmPurchaseReturnBtn').on('click', function() {
            allowSubmitPurchaseReturn = true;
            $('#confirmPurchaseReturnModal').modal('hide');
            $('#purchaseReturnForm')[0].submit();
        });

        $('#confirmPurchaseReturnModal').on('hidden.bs.modal', function() {
            allowSubmitPurchaseReturn = false;
        });

        $('#purchase_order_id').on('select2:select change', function(e) {
            const data = e.params && e.params.data ? e.params.data : null;

            if (data && data.id) {
                loadOrderDetails(data.id);
                return;
            }

            const currentVal = $(this).val();
            if (currentVal) {
                loadOrderDetails(currentVal);
            }
        });

        $(document).on('input', '.qty', function() {
            const tr = $(this).closest('tr');
            const max = parseInt($(this).attr('max')) || 0;
            let val = parseInt($(this).val()) || 0;

            if (val > max) {
                val = max;
                $(this).val(max);
                Swal.fire('Chú ý', 'Số lượng trả không được vượt quá số lượng còn được trả.', 'warning');
            }

            const price = parseFloat(tr.find('.price').val()) || 0;
            tr.find('.subtotal').text(formatMoney(val * price));
            calculateTotal();
        });

        @if($selectedOrder)
            loadOrderDetails({{ $selectedOrder->id }});
        @endif
    });
</script>
@endpush