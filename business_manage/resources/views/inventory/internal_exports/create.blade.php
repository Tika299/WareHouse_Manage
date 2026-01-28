@extends('layouts.app')

@section('content')
<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-file-export"></i> Lập Phiếu Xuất Kho Nội Bộ</h3>
    </div>
    <form action="{{ route('internal_exports.store') }}" method="POST" id="internalExportForm">
        @csrf
        <div class="card-body">
            <div class="row">
                <!-- THÔNG TIN CHUNG -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Lý do xuất kho <span class="text-danger">*</span></label>
                        <select name="reason_type" class="form-control" required>
                            <option value="Người nhà dùng">Người nhà dùng</option>
                            <option value="Dùng cho kinh doanh">Dùng cho mục đích kinh doanh</option>
                            <option value="Hàng biếu tặng">Hàng biếu tặng</option>
                            <option value="Hàng hư hỏng/Hết hạn">Hàng hư hỏng / Hết hạn</option>
                            <option value="Khác">Lý do khác</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Ghi chú chi tiết</label>
                        <input type="text" name="note" class="form-control" placeholder="Ví dụ: Xuất dùng cho văn phòng...">
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

            <table class="table table-bordered table-striped mt-3" id="itemTable">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150" class="text-center">Tồn kho hiện tại</th>
                        <th width="150" class="text-center">Số lượng xuất</th>
                        <th width="50" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="exportBody">
                    {{-- Dòng trống mặc định --}}
                    <tr id="empty-row">
                        <td colspan="4" class="text-center p-4 text-muted">Chưa có sản phẩm nào được chọn.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-danger px-5 font-weight-bold" id="btnSubmit">
                <i class="fas fa-check"></i> XÁC NHẬN XUẤT KHO
            </button>
        </div>
    </form>
</div>

@push('scripts')

<script>
    let rowIdx = 0;

    $(document).ready(function() {

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
                               name="items[${rowIdx}][quantity]"
                               class="form-control actual-qty text-center"
                               value="1" min="1" max="${data.stock}"
                               data-cost="${data.cost_price}"
                               required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#exportBody').append(html);
            rowIdx++;

            // 4. Reset ô tìm kiếm về trạng thái trống
            $(this).val(null).trigger('change');

            calculateTotal();
        });

        // Xóa dòng
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#exportBody tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
        });

        // Kiểm tra số lượng xuất ngay lúc nhập (Không cho xuất quá tồn)
        $(document).on('input', '.input-qty', function() {
            let max = parseInt($(this).attr('max'));
            let val = parseInt($(this).val());
            let errorMsg = $(this).siblings('.error-msg');

            if (val > max) {
                $(this).addClass('is-invalid');
                errorMsg.show();
                $('#btnSubmit').prop('disabled', true);
            } else {
                $(this).removeClass('is-invalid');
                errorMsg.hide();
                $('#btnSubmit').prop('disabled', false);
            }
        });

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