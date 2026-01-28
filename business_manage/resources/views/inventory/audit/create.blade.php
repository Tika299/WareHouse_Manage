@extends('layouts.app')

@section('content')
<div class="card card-dark">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Lập Phiếu Kiểm Kê Kho</h3>
    </div>
    <form action="{{ route('audits.store') }}" method="POST" id="auditForm">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Ghi chú kiểm kho</label>
                        <input type="text" name="note" class="form-control" placeholder="Ví dụ: Kiểm kê định kỳ tháng 12">
                    </div>
                </div>
                <!-- Ô TÌM KIẾM SẢN PHẨM (Component tự động init Select2) -->
                <div class="col-md-4">
                    <x-select2-ajax
                        name="search_product"
                        id="search_product" {{-- ID này sẽ được dùng trong Script --}}
                        label="Tìm sản phẩm kiểm kê"
                        :url="route('products.searchAjax')"
                        placeholder="Gõ tên hoặc mã SKU..." />
                </div>
            </div>

            <table class="table table-bordered table-striped mt-3" id="auditTable">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150" class="text-center">Tồn hệ thống</th>
                        <th width="180" class="text-center">Tồn thực tế</th>
                        <th width="120" class="text-center">Chênh lệch</th>
                        <th width="200" class="text-right">Giá trị lệch (Vốn)</th>
                        <th width="50" class="text-center">#</th>
                    </tr>
                </thead>
                <tbody id="auditBody">
                    <tr id="empty-row">
                        <td colspan="6" class="text-center p-4 text-muted">Chưa có sản phẩm nào được chọn. Vui lòng tìm ở ô phía trên.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="m-0">Tổng giá trị chênh lệch: <span id="total-diff-display" class="text-bold">0 đ</span></h5>
                </div>
                <div class="col-md-4 text-right">
                    <button type="submit" class="btn btn-dark btn-lg px-5 font-weight-bold">
                        <i class="fas fa-save"></i> XÁC NHẬN ĐIỀU CHỈNH KHO
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let rowIdx = 0;

    $(document).ready(function() {
        /**
         * Lắng nghe sự kiện SELECT từ component (ID: #search_product)
         * Không cần chạy lệnh .select2() nữa vì component đã làm rồi.
         */
        $('#search_product').on('select2:select', function(e) {
            let data = e.params.data; // Dữ liệu từ Trait Select2Searchable trả về

            // 1. Kiểm tra trùng sản phẩm
            if ($(`#row-${data.id}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách kiểm kê!', 'info');
                $(this).val(null).trigger('change');
                return;
            }

            // 2. Ẩn dòng thông báo "Trống"
            $('#empty-row').hide();

            // 3. Render dòng mới
            // Lưu ý: data.stock và data.cost_price lấy từ mảng map() trong Trait
            let html = `
                <tr id="row-${data.id}">
                    <td>
                        <b class="text-primary">${data.sku}</b> - ${data.name}
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                    </td>
                    <td class="text-center system-qty">${data.stock}</td>
                    <td>
                        <input type="number"
                               name="items[${rowIdx}][actual_qty]"
                               class="form-control actual-qty text-center"
                               value="${data.stock}"
                               data-cost="${data.cost_price}"
                               required>
                    </td>
                    <td class="text-center diff-qty">0</td>
                    <td class="text-right diff-value">0 đ</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#auditBody').append(html);
            rowIdx++;

            // 4. Reset ô tìm kiếm về trạng thái trống
            $(this).val(null).trigger('change');

            calculateTotal();
        });

        // ===============================
        // XÓA DÒNG
        // ===============================
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            // Nếu không còn dòng nào thì hiện lại thông báo trống
            if ($('#auditBody tr').length === 1 && $('#empty-row').length > 0) {
                $('#empty-row').show();
            } else if ($('#auditBody tr').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // ===============================
        // TÍNH TOÁN KHI NHẬP SỐ LƯỢNG THỰC TẾ
        // ===============================
        $(document).on('input', '.actual-qty', function() {
            let tr = $(this).closest('tr');
            let systemQty = parseInt(tr.find('.system-qty').text()) || 0;
            let actualQty = parseInt($(this).val()) || 0;
            let costPrice = parseFloat($(this).data('cost')) || 0;

            let diff = actualQty - systemQty;
            let diffValue = diff * costPrice;

            // Hiển thị chênh lệch (màu sắc)
            tr.find('.diff-qty')
                .text(diff)
                .removeClass('text-danger text-success')
                .addClass(diff < 0 ? 'text-danger' : (diff > 0 ? 'text-success' : ''));

            // Hiển thị giá trị lệch
            tr.find('.diff-value')
                .text(new Intl.NumberFormat('vi-VN').format(diffValue) + ' đ');

            calculateTotal();
        });

        // ===============================
        // TÍNH TỔNG PHIẾU
        // ===============================
        function calculateTotal() {
            let totalValue = 0;

            $('.actual-qty').each(function() {
                let tr = $(this).closest('tr');
                let systemQty = parseInt(tr.find('.system-qty').text()) || 0;
                let actualQty = parseInt($(this).val()) || 0;
                let costPrice = parseFloat($(this).data('cost')) || 0;

                totalValue += (actualQty - systemQty) * costPrice;
            });

            let color = totalValue < 0 ? 'text-danger' : (totalValue > 0 ? 'text-success' : '');
            $('#total-diff-display')
                .text(new Intl.NumberFormat('vi-VN').format(totalValue) + ' đ')
                .removeClass('text-danger text-success')
                .addClass(color);
        }
    });
</script>
@endpush
@endsection