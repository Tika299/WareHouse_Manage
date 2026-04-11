@extends('layouts.app')
@section('title', 'Trả hàng theo hóa đơn')

@section('content')
<form action="{{ route('customer_returns.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-warning shadow">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-undo"></i> Danh sách hàng trả</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-light text-13">
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th width="120" class="text-center">Đã mua</th>
                                <th width="150" class="text-center">Số lượng trả</th>
                                <th width="150" class="text-right">Giá hoàn (đ)</th>
                                <th width="150" class="text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="return-body">
                            <tr id="empty-row">
                                <td colspan="5" class="text-center p-5 text-muted">Vui lòng chọn đơn hàng bên phải để bắt đầu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-warning shadow">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Thông tin đơn hàng</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tìm đơn hàng <span class="text-danger">*</span></label>
                        <x-select2-ajax
                            name="sales_order_id" id="sales_order_id"
                            :url="route('customer_returns.searchOrdersAjax')"
                            placeholder="Nhập mã #DH hoặc tên khách..."
                            required="true" />
                    </div>

                    <div id="customer-info" class="alert alert-info py-2" style="display:none">
                        Khách hàng: <b id="customer-name-display">-</b>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú lý do trả</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Hàng lỗi, khách đổi ý..."></textarea>
                    </div>

                    <div class="bg-dark p-3 rounded text-center">
                        <small class="text-muted text-uppercase">Số tiền trừ vào nợ gộp</small>
                        <h2 class="text-warning font-weight-bold mb-0" id="total-display">0 đ</h2>
                    </div>

                    <button type="submit" class="btn btn-warning btn-block btn-lg mt-3 font-weight-bold">
                        <i class="fas fa-check-circle"></i> XÁC NHẬN TRẢ HÀNG
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(document).ready(function() {
        // Lắng nghe sự kiện khi CHỌN đơn hàng từ Select2

        $('#sales_order_id').on('select2:select', function(e) {
            let data = e.params.data;
            let orderId = data.id;

            $('#customer-info').show();
            $('#customer-name-display').text(data.customer_name);

            $('#return-body').html('<tr><td colspan="5" class="text-center">Đang tải sản phẩm...</td></tr>');

            // Sửa URL tại đây để chắc chắn nó gọi đúng vào Controller
            $.ajax({
                url: "{{ url('sales/returns/get-order-details') }}/" + orderId, // Đảm bảo URL này khớp web.php
                method: "GET",
                success: function(details) {
                    console.log("Danh sách SP trả về:", details); // Kiểm tra xem 'product' có bị null không
                    $('#return-body').empty();

                    $.each(details, function(index, item) {
                        // Phải kiểm tra item.product vì bạn đang dùng with('details.product')
                        let pName = item.product ? item.product.name : 'Sản phẩm đã bị xóa';
                        let pSku = item.product ? item.product.sku : '---';

                        let html = `
                <tr>
                    <td>
                        <b>${pSku}</b> - ${pName}
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                    </td>
                    <td class="text-center">${item.quantity}</td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" 
                               class="form-control qty text-center" 
                               value="0" min="0" max="${item.quantity}">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][refund_price]" 
                               class="form-control price text-right" 
                               value="${item.unit_price}" readonly>
                    </td>
                    <td class="text-right subtotal">0 đ</td>
                </tr>`;
                        $('#return-body').append(html);
                    });
                },
                error: function(xhr) {
                    alert("Lỗi không lấy được chi tiết đơn hàng!");
                    console.log(xhr.responseText);
                }
            });
        });

        // Tính toán tiền (giữ nguyên logic của bạn)
        $(document).on('input', '.qty', function() {
            let tr = $(this).closest('tr');
            let q = parseInt($(this).val()) || 0;
            let p = parseFloat(tr.find('.price').val()) || 0;
            let max = parseInt($(this).attr('max'));

            if (q > max) {
                $(this).val(max);
                q = max;
                Swal.fire('Chú ý', 'Không thể trả quá số lượng đã mua!', 'warning');
            }

            tr.find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(q * p) + ' đ');
            calculateTotal();
        });

        function calculateTotal() {
            let total = 0;
            $('.qty').each(function() {
                let tr = $(this).closest('tr');
                let q = parseInt($(this).val()) || 0;
                let p = parseFloat(tr.find('.price').val()) || 0;
                total += (q * p);
            });
            $('#total-display').text(new Intl.NumberFormat('vi-VN').format(total) + ' đ');
        }
    });
</script>
@endpush
@endsection