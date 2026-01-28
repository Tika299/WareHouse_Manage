@extends('layouts.app')
@section('content')
<form action="{{ route('imports.store') }}" method="POST" id="importForm">
    @csrf
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">Lập Phiếu Nhập Hàng</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Nhà cung cấp <span class="text-danger">*</span></label>
                    <div class="input-group" style="flex-wrap: nowrap;">
                        <x-select2-ajax
                            name="supplier_id"
                            id="supplier_id"
                            :url="route('providers.searchAjax')"
                            placeholder="-- Tìm NCC --"
                            required="true" />
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddSupplier">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>Chi từ tài khoản</label>
                    <select name="account_id" class="form-control form-control-sm">
                        @foreach($accounts as $a) <option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->current_balance) }}đ)</option> @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Phí phát sinh</label>
                    <input type="number" name="extra_cost" id="extra_cost" class="form-control form-control-sm" value="0">
                </div>
                <div class="col-md-2">
                    <label>Thanh toán ngay</label>
                    <input type="number" name="paid_amount" class="form-control form-control-sm" value="0">
                </div>

                <!-- Ô TÌM KIẾM SẢN PHẨM (Component tự động init Select2) -->
                <div class="col-md-2">
                    <x-select2-ajax
                        name="search_product"
                        id="search_product"
                        label="Tìm sản phẩm"
                        :url="route('products.searchAjax')"
                        placeholder="Gõ tên/SKU..." />
                </div>
            </div>

            <table class="table table-bordered mt-4" id="productTable">
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
                        <td colspan="5" class="text-center p-4 text-muted">Chưa có sản phẩm nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <input type="hidden" name="total_product_value" id="total_product_value" value="0">
            <h4 class="text-right">Tổng tiền hàng: <span id="display_total" class="text-danger">0</span> đ</h4>
            <button type="submit" class="btn btn-primary float-right px-5 font-weight-bold">XÁC NHẬN NHẬP KHO</button>
        </div>
    </div>
</form>

{{-- Modal Thêm NCC nhanh giữ nguyên của bạn --}}

@push('scripts')
<script>
    let rowIdx = 0;

    $(document).ready(function() {
        // Lắng nghe sự kiện Select từ Component (ID là #search_product)
        $('#search_product').on('select2:select', function(e) {
            let data = e.params.data; // Dữ liệu trả về từ Trait

            // 1. Kiểm tra trùng
            if ($(`#row-${data.id}`).length > 0) {
                Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
                $(this).val(null).trigger('change');
                return;
            }

            // 2. Ẩn dòng trống
            $('#empty-row').hide();

            // 3. Thêm dòng mới vào table
            let html = `
                <tr id="row-${data.id}">
                    <td>
                        <b class="text-primary">${data.sku}</b> - ${data.name} 
                        <br><small class="text-muted">Tồn kho hiện tại: ${data.stock}</small>
                        <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][quantity]" class="form-control qty text-center" value="1" min="1">
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][import_price]" class="form-control price text-center" value="${data.cost_price}">
                    </td>
                    <td class="text-right subtotal font-weight-bold">${new Intl.NumberFormat('vi-VN').format(data.cost_price)} đ</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;

            $('#productBody').append(html);
            rowIdx++;

            // 4. Reset ô tìm kiếm
            $(this).val(null).trigger('change');
            calculateTotal();
        });

        // Xóa dòng
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#productBody tr:visible').length === 0) {
                $('#empty-row').show();
            }
            calculateTotal();
        });

        // Tính toán khi thay đổi SL hoặc Giá
        $(document).on('input', '.qty, .price', function() {
            let tr = $(this).closest('tr');
            let q = parseFloat(tr.find('.qty').val()) || 0;
            let p = parseFloat(tr.find('.price').val()) || 0;
            tr.find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(q * p) + ' đ');
            calculateTotal();
        });

        function calculateTotal() {
            let grandTotal = 0;
            $('#productBody tr:visible').each(function() {
                let q = parseFloat($(this).find('.qty').val()) || 0;
                let p = parseFloat($(this).find('.price').val()) || 0;
                grandTotal += (q * p);
            });
            $('#total_product_value').val(grandTotal);
            $('#display_total').text(new Intl.NumberFormat('vi-VN').format(grandTotal));
        }
    });
</script>
@endpush
@endsection