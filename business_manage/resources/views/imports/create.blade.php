@extends('layouts.app')
@section('content')
<form action="{{ route('imports.store') }}" method="POST" id="importForm">
    @csrf
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Lập Phiếu Nhập Hàng</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <!-- Chỗ chọn Nhà cung cấp -->
                    <div class="form-group">
                        <label>Nhà cung cấp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                <option value="">-- Chọn NCC --</option>
                                @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddSupplier">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>Chi từ tài khoản</label>
                    <select name="account_id" class="form-control">
                        @foreach($accounts as $a) <option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->current_balance) }})</option> @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Phí phát sinh (Ship/Thuế)</label>
                    <input type="number" name="extra_cost" id="extra_cost" class="form-control" value="0">
                </div>
                <div class="col-md-3">
                    <label>Thanh toán ngay</label>
                    <input type="number" name="paid_amount" class="form-control" value="0">
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



            <table class="table table-bordered mt-4" id="productTable">
                <thead>
                    <tr class="bg-light">
                        <th>Sản phẩm</th>
                        <th width="150">Số lượng</th>
                        <th width="200">Giá nhập NCC</th>
                        <th width="200">Thành tiền</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Sản phẩm sẽ được thêm vào đây bằng JavaScript --}}
                    <tr id="empty-row">
                        <td colspan="4" class="text-center p-4 text-muted">Chưa có sản phẩm nào được chọn. Vui lòng tìm ở ô phía trên.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <input type="hidden" name="total_product_value" id="total_product_value" value="0">
            <h4 class="text-right">Tổng tiền hàng: <span id="display_total">0</span> đ</h4>
            <button type="submit" class="btn btn-primary float-right">Xác nhận Nhập kho</button>
        </div>
    </div>
</form>

<!-- MODAL THÊM NHÀ CUNG CẤP NHANH -->
<div class="modal fade" id="modalAddSupplier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">Thêm Nhà Cung Cấp Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAddSupplierForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="new_supplier_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" id="new_supplier_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" id="new_supplier_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btnSaveSupplier">Lưu NCC</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Thêm NCC nhanh trong modal
    $(document).ready(function() {
        $('#quickAddSupplierForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btnSaveSupplier');
            btn.prop('disabled', true).text('Đang lưu...');

            $.ajax({
                url: "{{ route('providers.store') }}", // Chúng ta sẽ dùng chung hàm store của NCC
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    // 1. Thêm NCC mới vào Dropdown hiện tại
                    let newOption = new Option(response.data.name, response.data.id, true, true);
                    $('#supplier_id').append(newOption).trigger('change');

                    // 2. Đóng modal và reset form
                    $('#modalAddSupplier').modal('hide');
                    $('#quickAddSupplierForm')[0].reset();

                    // 3. Thông báo thành công
                    Swal.fire('Thành công', 'Đã thêm nhà cung cấp mới', 'success');
                },
                error: function(xhr) {
                    let error = xhr.responseJSON.message || 'Có lỗi xảy ra, vui lòng kiểm tra lại.';
                    Swal.fire('Lỗi', error, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Lưu NCC');
                }
            });
        });
    });

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
            let stock = data.stock_quantity || 0; // Sử dụng stock_quantity nếu có

            // Không cho trùng sản phẩm
            if ($(`#row-${productId}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
                $('#productSearch').val(null).trigger('change');
                return;
            }

            $('#empty-row').hide();

            let html = `
                <tr id="row-${productId}">
                    <td>
                        <b>${productSku || ''}</b> - ${productName} (Tồn: ${stock})
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${productId}">
                    </td>
                    <td>
                        <input type="number"
                               name="items[${rowIdx}][quantity]"
                               class="form-control qty text-center"
                               value="1"
                               min="1"
                               required>
                    </td>
                    <td>
                        <input type="number"
                               name="items[${rowIdx}][import_price]"
                               class="form-control price text-center"
                               value="0"
                               min="0"
                               required>
                    </td>
                    <td class="text-right subtotal">0 đ</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-danger btn-remove"
                                data-id="${productId}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#productTable tbody').append(html);
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
            if ($('#productTable tbody tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // ===============================
        // CALCULATE
        // ===============================
        $(document).on('input', '.qty, .price', function() {
            let tr = $(this).closest('tr');
            let qty = parseFloat(tr.find('.qty').val()) || 0;
            let price = parseFloat(tr.find('.price').val()) || 0;
            let sub = qty * price;

            tr.find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');

            calculateTotal();
        });

        function calculateTotal() {
            let grandTotal = 0;

            $('.qty').each(function() {
                let tr = $(this).closest('tr');
                let qty = parseFloat($(this).val()) || 0;
                let price = parseFloat(tr.find('.price').val()) || 0;
                grandTotal += qty * price;
            });

            $('#total_product_value').val(grandTotal);
            $('#display_total').text(new Intl.NumberFormat('vi-VN').format(grandTotal));
        }
    });
</script>
@endsection