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
                <!-- Ô TÌM KIẾM SẢN PHẨM (Bây giờ dùng Ajax) -->
                <div class="col-md-4">
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

            // Thêm dòng mới
            let html = `
                <tr id="row-${productId}">
                    <td>
                        <b>${productSku}</b> - ${productName}
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${productId}">
                    </td>
                    <td class="text-center text-bold text-primary" style="line-height: 38px;">${stock}</td>
                    <td>
                        <input type="number" name="items[${rowIdx}][quantity]" 
                               class="form-control text-center input-qty" 
                               value="1" min="1" max="${stock}" 
                               required>
                        <small class="text-danger error-msg" style="display:none">Vượt quá tồn kho!</small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;

            $('#itemTable').append(html);
            rowIdx++;

            // Reset search
            $('#productSearch').val(null).trigger('change');

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
    });
</script>
@endpush
@endsection