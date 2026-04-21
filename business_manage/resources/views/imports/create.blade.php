@extends('layouts.app')

@section('content')
<form action="{{ route('imports.store') }}" method="POST" id="importForm">
    @csrf
    <div class="card card-primary card-outline shadow">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-file-import"></i> Lập Phiếu Nhập Hàng</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- NHÀ CUNG CẤP -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Nhà cung cấp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="flex-grow-1">
                                <x-select2-ajax
                                    name="supplier_id"
                                    id="supplier_id"
                                    label=""
                                    :url="route('providers.searchAjax')"
                                    placeholder="-- Tìm NCC --"
                                    required="true" />
                            </div>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddSupplier">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TÀI KHOẢN -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Chi từ tài khoản <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="account_id" id="account_id" class="form-control form-control-sm">
                                @foreach($accounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->current_balance) }}đ)</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddAccount">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHÍ PHÁT SINH -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Phí phát sinh (Ship/Thuế)</label>
                        <input type="number" name="extra_cost" id="extra_cost" class="form-control text-primary font-weight-bold" value="0">
                    </div>
                </div>

                <!-- THANH TOÁN NGAY -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Thanh toán ngay</label>
                        <input type="number" name="paid_amount" class="form-control text-success font-weight-bold" value="0">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="text-primary font-weight-bold mb-0">Danh sách sản phẩm nhập</h5>
                </div>
                <!-- TÌM SẢN PHẨM -->
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="flex-grow-1">
                            <x-select2-ajax
                                name="search_product"
                                id="search_product"
                                label=""
                                :url="route('products.searchAjax')"
                                placeholder="Gõ tên hoặc SKU để thêm sản phẩm..." />
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#quickAddProductModal">
                                <i class="fas fa-plus-circle"></i> Thêm mới SP
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-striped mt-3" id="productTable">
                <thead>
                    <tr class="bg-light text-13">
                        <th>Sản phẩm</th>
                        <th width="120" class="text-center">Số lượng</th>
                        <th width="180" class="text-center">Giá nhập NCC</th>
                        <th width="180" class="text-center">Thành tiền</th>
                        <th width="50" class="text-center">#</th>
                    </tr>
                </thead>
                <tbody id="productBody">
                    <tr id="empty-row">
                        <td colspan="5" class="text-center p-4 text-muted">Chưa có sản phẩm nào. Hãy tìm ở ô phía trên hoặc thêm mới.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top">
            <input type="hidden" name="total_product_value" id="total_product_value" value="0">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-muted">Lưu ý: Giá vốn sẽ được tính lại theo phương pháp Bình quân gia quyền sau khi nhập kho.</span>
                </div>
                <div class="col-md-4 text-right">
                    <h4 class="mb-2">Tổng tiền hàng: <span id="display_total" class="text-danger font-weight-bold">0</span> đ</h4>
                    <button type="submit" class="btn btn-primary btn-lg px-5 font-weight-bold">XÁC NHẬN NHẬP KHO</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL THÊM NHÀ CUNG CẤP --}}
<div class="modal fade" id="modalAddSupplier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-truck"></i> Thêm Nhà Cung Cấp Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <form id="quickAddSupplierForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên nhà cung cấp <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
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

{{-- MODAL THÊM SẢN PHẨM --}}
<x-modal-quick-add-product />

@push('scripts')
<script>
    let rowIdx = 0;

    // Hàm dùng chung để thêm sản phẩm vào bảng
    function addProductToTable(data) {
        if ($(`#row-${data.id}`).length > 0) {
            Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
            return;
        }

        $('#empty-row').hide();

        let html = `
            <tr id="row-${data.id}">
                <td>
                    <b class="text-primary">${data.sku}</b> - ${data.name} 
                    <br><small class="text-muted italic">Tồn kho hiện tại: ${data.stock}</small>
                    <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][quantity]" class="form-control qty text-center" value="1" min="1">
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][import_price]" class="form-control price text-center" value="${data.cost_price || 0}">
                </td>
                <td class="text-right subtotal font-weight-bold text-dark">
                    ${new Intl.NumberFormat('vi-VN').format(data.cost_price || 0)} đ
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;

        $('#productBody').append(html);
        rowIdx++;
        calculateTotal();
    }

    function calculateTotal() {
        let grandTotal = 0;
        $('#productBody tr:visible').each(function() {
            if ($(this).attr('id') === 'empty-row') return;
            let q = parseFloat($(this).find('.qty').val()) || 0;
            let p = parseFloat($(this).find('.price').val()) || 0;
            grandTotal += (q * p);
        });
        $('#total_product_value').val(grandTotal);
        $('#display_total').text(new Intl.NumberFormat('vi-VN').format(grandTotal));
    }

    $(document).ready(function() {
        // Logic xử lý Submit Form thêm Tài khoản nhanh
        $('#quickAddAccountForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnSaveAccount');
            let form = $(this);

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

            $.ajax({
                url: "{{ route('accounts.store') }}",
                method: "POST",
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // 1. Tạo định dạng hiển thị số tiền
                        let formattedBalance = new Intl.NumberFormat('vi-VN').format(response.data.current_balance);

                        // 2. Thêm tài khoản mới vào ô select và chọn luôn nó
                        let text = response.data.name + " (" + formattedBalance + "đ)";
                        let newOption = new Option(text, response.data.id, true, true);
                        $('#account_id').append(newOption).trigger('change');

                        // 3. Đóng modal và reset form
                        $('#modalAddAccount').modal('hide');
                        form[0].reset();

                        Swal.fire('Thành công', 'Đã thêm tài khoản mới!', 'success');
                    }
                },
                error: function(xhr) {
                    let error = xhr.responseJSON.message || 'Lỗi: Tên tài khoản có thể đã tồn tại.';
                    Swal.fire('Lỗi', error, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Lưu tài khoản');
                }
            });
        });

        // 1. CHỌN SẢN PHẨM TỪ SEARCH
        $('#search_product').on('select2:select', function(e) {
            addProductToTable(e.params.data);
            $(this).val(null).trigger('change');
        });

        // 2. THÊM NHANH NHÀ CUNG CẤP
        $('#quickAddSupplierForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnSaveSupplier');
            btn.prop('disabled', true).text('Đang lưu...');
            $.ajax({
                url: "{{ route('providers.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    let newOption = new Option(response.data.name, response.data.id, true, true);
                    $('#supplier_id').append(newOption).trigger('change');
                    $('#modalAddSupplier').modal('hide');
                    $('#quickAddSupplierForm')[0].reset();
                    Swal.fire('Thành công', 'Đã thêm nhà cung cấp', 'success');
                },
                error: function(xhr) {
                    Swal.fire('Lỗi', 'Không thể thêm NCC', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Lưu NCC');
                }
            });
        });

        // 4. XÓA DÒNG
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#productBody tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // 5. TÍNH TOÁN KHI NHẬP SL/GIÁ
        $(document).on('input', '.qty, .price', function() {
            let tr = $(this).closest('tr');
            let q = parseFloat(tr.find('.qty').val()) || 0;
            let p = parseFloat(tr.find('.price').val()) || 0;
            tr.find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(q * p) + ' đ');
            calculateTotal();
        });
    });
</script>
@endpush
@endsection