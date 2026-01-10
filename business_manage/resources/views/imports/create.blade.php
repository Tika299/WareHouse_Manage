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
            </div>

            <table class="table table-bordered mt-4" id="productTable">
                <thead>
                    <tr class="bg-light">
                        <th>Sản phẩm</th>
                        <th width="150">Số lượng</th>
                        <th width="200">Giá nhập NCC</th>
                        <th width="200">Thành tiền</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-control">
                                @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }} (Tồn: {{ $p->stock_quantity }})</option> @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control qty" value="1"></td>
                        <td><input type="number" name="items[0][import_price]" class="form-control price" value="0"></td>
                        <td class="text-right subtotal">0 đ</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-info" id="addRow">+ Thêm sản phẩm</button>
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

@push('scripts')
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

    let rowIdx = 1;
    $('#addRow').click(function() {
        let html = `<tr>
            <td><select name="items[${rowIdx}][product_id]" class="form-control">@foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach</select></td>
            <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control qty" value="1"></td>
            <td><input type="number" name="items[${rowIdx}][import_price]" class="form-control price" value="0"></td>
            <td class="text-right subtotal">0 đ</td>
            <td><button type="button" class="btn btn-danger btn-xs removeRow">x</button></td>
        </tr>`;
        $('#productTable tbody').append(html);
        rowIdx++;
    });

    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        calculateTotal();
    });
    $(document).on('input', '.qty, .price', function() {
        calculateTotal();
    });

    function calculateTotal() {
        let grandTotal = 0;
        $('#productTable tbody tr').each(function() {
            let q = parseFloat($(this).find('.qty').val()) || 0;
            let p = parseFloat($(this).find('.price').val()) || 0;
            let sub = q * p;
            $(this).find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');
            grandTotal += sub;
        });
        $('#total_product_value').val(grandTotal);
        $('#display_total').text(new Intl.NumberFormat('vi-VN').format(grandTotal));
    }
</script>
@endpush
@endsection