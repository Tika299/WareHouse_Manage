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
                <!-- Ô TÌM KIẾM SẢN PHẨM (Bây giờ dùng Ajax) -->
                <div class="col-md-3">
                    <div class="">
                        <label class="form-label fw-semibold text-primary mb-2">
                            Tìm sản phẩm
                        </label>

                        <div class="input-group" style="flex-wrap: nowrap;">
                            <span class="input-group-text bg-primary" style="border-radius: 0; border-top-left-radius: .25rem; border-bottom-left-radius: .25rem; border: 0;">
                                <i class="bi bi-search"></i>
                            </span>
                            <select id="productSearch" class="form-select select2" style="padding-left: 10px; border: 1px solid #ced4da; width: 100%; border-top-right-radius: .25rem; border-bottom-right-radius: .25rem;">
                                <option value="">Nhập tên hoặc mã SKU...</option>
                            </select>
                        </div>

                        <small class="text-muted mt-2 d-block">
                            Chọn sản phẩm để thêm vào danh sách
                        </small>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped" id="auditTable">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150" class="text-center">Tồn hệ thống</th>
                        <th width="180" class="text-center">Tồn thực tế</th>
                        <th width="120" class="text-center">Chênh lệch</th>
                        <th width="200" class="text-right">Giá trị lệch (Vốn)</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody id="auditBody">
                    {{-- Sản phẩm sẽ được thêm vào đây bằng JavaScript --}}
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
                    <button type="submit" class="btn btn-dark btn-lg px-5">XÁC NHẬN ĐIỀU CHỈNH KHO</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let rowIdx = 0;

    $(document).ready(function() {

        // ===============================
        // SELECT2 SEARCH - FIX RACE CONDITION
        // ===============================
        $('#productSearch').select2({
            theme: 'bootstrap4',
            placeholder: "Nhập tên hoặc mã SKU...",
            allowClear: true,
            minimumInputLength: 0, //  cho phép focus & gõ
            ajax: {
                url: '{{ route("products.search") }}',
                dataType: 'json',
                delay: 300,

                //  CHẶN REQUEST KHI < 2 KÝ TỰ
                data: function(params) {
                    if (!params.term || params.term.trim().length < 2) {
                        return {
                            search: '__EMPTY__' // gửi dummy để Select2 không cache kết quả cũ
                        };
                    }

                    return {
                        search: params.term.trim()
                    };
                },

                processResults: function(data, params) {
                    // Nếu keyword không hợp lệ → không render gì
                    if (!params.term || params.term.trim().length < 2) {
                        return {
                            results: []
                        };
                    }

                    return {
                        results: data.results
                    };
                },

                cache: false
            }
        });

        // Khi clear → đóng dropdown cho sạch UI
        $('#productSearch').on('select2:clear', function() {
            $(this).select2('close');
        });

        // ===============================
        // KHI CHỌN SẢN PHẨM
        // ===============================
        $('#productSearch').on('select2:select', function(e) {
            let data = e.params.data;

            let productId = data.id;
            let productName = data.name;
            let productSku = data.sku;
            let stock = data.stock;
            let cost = data.cost;

            // Không cho trùng sản phẩm
            if ($(`#row-${productId}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách kiểm kê!', 'info');
                $('#productSearch').val(null).trigger('change');
                return;
            }

            $('#empty-row').hide();

            let html = `
                <tr id="row-${productId}">
                    <td>
                        <b>${productSku}</b> - ${productName}
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${productId}">
                    </td>
                    <td class="text-center system-qty">${stock}</td>
                    <td>
                        <input type="number"
                               name="items[${rowIdx}][actual_qty]"
                               class="form-control actual-qty text-center"
                               value="${stock}"
                               data-cost="${cost}"
                               required>
                    </td>
                    <td class="text-center diff-qty">0</td>
                    <td class="text-right diff-value">0 đ</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-danger btn-remove"
                                data-id="${productId}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#auditBody').append(html);
            rowIdx++;

            // Reset search
            $('#productSearch').val(null).trigger('change');

            calculateTotal();
        });

        // ===============================
        // REMOVE ROW
        // ===============================
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#auditBody tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // ===============================
        // CALCULATE
        // ===============================
        $(document).on('input', '.actual-qty', function() {
            let tr = $(this).closest('tr');
            let systemQty = parseInt(tr.find('.system-qty').text());
            let actualQty = parseInt($(this).val()) || 0;
            let costPrice = parseFloat($(this).data('cost'));

            let diff = actualQty - systemQty;
            let diffValue = diff * costPrice;

            tr.find('.diff-qty')
                .text(diff)
                .removeClass('text-danger text-success')
                .addClass(diff < 0 ? 'text-danger' : diff > 0 ? 'text-success' : '');

            tr.find('.diff-value')
                .text(new Intl.NumberFormat('vi-VN').format(diffValue) + ' đ');

            calculateTotal();
        });

        function calculateTotal() {
            let total = 0;

            $('.actual-qty').each(function() {
                let tr = $(this).closest('tr');
                let systemQty = parseInt(tr.find('.system-qty').text());
                let actualQty = parseInt($(this).val()) || 0;
                let costPrice = parseFloat($(this).data('cost'));

                total += (actualQty - systemQty) * costPrice;
            });

            let color = total < 0 ? 'text-danger' : total > 0 ? 'text-success' : '';
            $('#total-diff-display')
                .text(new Intl.NumberFormat('vi-VN').format(total) + ' đ')
                .removeClass('text-danger text-success')
                .addClass(color);
        }
    });
</script>
@endsection