@extends('layouts.app')

@section('title', 'Lập phiếu kiểm kê kho')

@section('content')
<div class="card card-dark shadow">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i>LẬP PHIẾU KIỂM KÊ KHO</h3>
    </div>
    <form action="{{ route('audits.store') }}" method="POST" id="auditForm">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Ghi chú kiểm kho</label>
                        <input type="text" name="note" class="form-control" placeholder="Ví dụ: Kiểm kê định kỳ kho tháng 04/2026">
                    </div>
                </div>
                <!-- Ô TÌM KIẾM SẢN PHẨM -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-primary">Tìm sản phẩm kiểm kê</label>
                        <x-select2-ajax
                            name="search_product_audit"
                            id="search_product_audit"
                            label=""
                            :url="route('audits.searchProducts')"
                            placeholder="Gõ tên, mã SKU hoặc biến thể..." />
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2" id="auditTable">
                    <thead class="bg-light text-12 text-uppercase">
                        <tr>
                            <th>Sản phẩm / SKU</th>
                            <th width="150" class="text-center">Tồn hệ thống</th>
                            <th width="180" class="text-center">Tồn thực tế</th>
                            <th width="120" class="text-center">Chênh lệch</th>
                            <th width="200" class="text-right">Giá trị lệch (Vốn)</th>
                            <th width="50" class="text-center">#</th>
                        </tr>
                    </thead>
                    <tbody id="auditBody">
                        <tr id="empty-row">
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fas fa-search mr-2"></i>Chưa có sản phẩm nào được chọn. Vui lòng tìm ở ô phía trên.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="m-0">TỔNG GIÁ TRỊ CHÊNH LỆCH: <span id="total-diff-display" class="text-bold text-lg">0 đ</span></h5>
                    <small class="text-muted italic">* Giá trị lệch = (Tồn thực tế - Tồn hệ thống) x Giá vốn BQGQ</small>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('audits.index') }}" class="btn btn-default mr-2">Hủy bỏ</a>
                    <button type="submit" class="btn btn-dark btn-lg px-5 font-weight-bold shadow">
                        <i class="fas fa-save mr-2"></i> XÁC NHẬN ĐIỀU CHỈNH
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
        // 1. Lắng nghe sự kiện chọn sản phẩm từ Component
        $('#search_product_audit').on('select2:select', function(e) {
            let data = e.params.data;

            // Kiểm tra trùng sản phẩm trong bảng
            if ($(`#row-${data.id}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách kiểm kê!', 'info');
                $(this).val(null).trigger('change');
                return;
            }

            // Ẩn dòng "Trống"
            $('#empty-row').hide();

            // Thêm dòng mới vào bảng
            let html = `
                <tr id="row-${data.id}">
                    <td>
                        <b class="text-primary">${data.sku}</b> - ${data.name}
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                    </td>
                    <td class="text-center text-bold system-qty">${data.stock}</td>
                    <td>
                        <input type="number"
                               name="items[${rowIdx}][actual_qty]"
                               class="form-control actual-qty text-center font-weight-bold"
                               value="${data.stock}"
                               data-cost="${data.cost_price}"
                               required>
                    </td>
                    <td class="text-center text-bold diff-qty">0</td>
                    <td class="text-right text-bold diff-value">0 đ</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#auditBody').append(html);
            rowIdx++;

            // Reset ô tìm kiếm
            $(this).val(null).trigger('change');

            calculateTotal();
        });

        // 2. Xử lý nút Xóa dòng
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#auditBody tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // 3. Tính toán Real-time khi nhập Tồn thực tế
        $(document).on('input', '.actual-qty', function() {
            let tr = $(this).closest('tr');
            let systemQty = parseInt(tr.find('.system-qty').text()) || 0;
            let actualQty = parseInt($(this).val()) || 0;
            let costPrice = parseFloat($(this).data('cost')) || 0;

            let diff = actualQty - systemQty;
            let diffValue = diff * costPrice;

            // Cập nhật hiển thị Chênh lệch (Số lượng)
            let diffCell = tr.find('.diff-qty');
            diffCell.text(diff > 0 ? '+' + diff : diff);
            diffCell.removeClass('text-danger text-success');
            if(diff < 0) diffCell.addClass('text-danger');
            if(diff > 0) diffCell.addClass('text-success');

            // Cập nhật hiển thị Giá trị lệch (Tiền)
            tr.find('.diff-value').text(new Intl.NumberFormat('vi-VN').format(diffValue) + ' đ');

            calculateTotal();
        });

        // 4. Hàm tính tổng toàn phiếu
        function calculateTotal() {
            let totalValue = 0;

            $('.actual-qty').each(function() {
                let tr = $(this).closest('tr');
                let systemQty = parseInt(tr.find('.system-qty').text()) || 0;
                let actualQty = parseInt($(this).val()) || 0;
                let costPrice = parseFloat($(this).data('cost')) || 0;

                totalValue += (actualQty - systemQty) * costPrice;
            });

            let display = $('#total-diff-display');
            display.text(new Intl.NumberFormat('vi-VN').format(totalValue) + ' đ');
            
            display.removeClass('text-danger text-success');
            if (totalValue < 0) display.addClass('text-danger');
            if (totalValue > 0) display.addClass('text-success');
        }
    });
</script>
@endpush

<style>
    .text-12 { font-size: 12px; }
    .italic { font-style: italic; }
    #auditTable input.actual-qty { border: 1px solid #6c757d; }
    #auditTable input.actual-qty:focus { border-color: #343a40; box-shadow: none; }
</style>
@endsection