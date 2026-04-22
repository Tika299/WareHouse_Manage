@extends('layouts.app')
@section('title', 'Tạo đơn hàng mới')

@section('content')
<form action="{{ route('exports.store') }}" method="POST" id="exportForm">
    @csrf
    <div class="row">
        <!-- Cột trái: Sản phẩm -->
        <div class="col-md-8">
            <div class="card card-success card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold">Sản phẩm xuất kho</h3>
                    <!-- Ô TÌM KIẾM NẰM RIÊNG ĐỂ TỐI ƯU -->
                    <div style="width: 350px;" class="ml-auto">
                        <div class="input-group" style="flex-wrap: nowrap;">
                            <x-select2-ajax
                                name="product_search"
                                id="product_search"
                                label=""
                                :url="route('products.searchAjax')"
                                placeholder="Gõ tên hoặc mã SKU để thêm..." />
                            <button type="button" class="btn btn-success ms-1" data-toggle="modal" data-target="#quickAddProductModal" title="Thêm nhanh sản phẩm mới">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="order-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="80" class="text-center">Tồn</th>
                                <th width="100" class="text-center">Số lượng</th>
                                <th width="150" class="text-center">Đơn giá</th>
                                <th width="150" class="text-center">Thành tiền</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="order-body">
                            <tr id="empty-row">
                                <td colspan="6" class="text-center p-4 text-muted">Chưa có sản phẩm nào. Hãy tìm ở ô phía trên.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thanh toán & Vận chuyển -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Thanh toán & Vận chuyển</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Khách hàng <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="flex-grow-1">
                                <x-select2-ajax
                                    name="customer_id"
                                    id="customer_id"
                                    label=""
                                    :url="route('customers.searchAjax')"
                                    placeholder="-- Tìm khách hàng --"
                                    required="true" />
                            </div>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddCustomer">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Đơn vị vận chuyển</label>
                        <div class="input-group">
                            <select name="shipping_unit_id" id="shipping_unit_id" class="form-control form-control-sm">
                                @foreach($shippingUnits as $su)
                                <option value="{{ $su->id }}">{{ $su->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddShipping">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Phí ship</label>
                                <input type="number" name="shipping_fee" id="shipping_fee" class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Người trả ship</label>
                                <select name="shipping_payor" id="shipping_payor" class="form-control form-control-sm">
                                    <option value="customer">Khách trả</option>
                                    <option value="shop">Shop trả</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tài khoản nhận tiền</label>
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

                    <div class="form-group">
                        <label>Tiền khách trả trước</label>
                        <input type="number" name="paid_amount" id="paid_amount" class="form-control form-control-lg text-success" value="0">
                    </div>

                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between"><span>Tiền hàng:</span> <b id="sum-product">0 đ</b></div>
                        <div class="d-flex justify-content-between" id="ship-row"><span>Phí ship:</span> <b id="sum-ship">0 đ</b></div>
                        <hr>
                        <div class="d-flex justify-content-between text-danger h4 font-weight-bold">
                            <span>TỔNG CỘNG:</span> <span id="sum-final">0 đ</span>
                        </div>
                        <div class="d-flex justify-content-between text-primary">
                            <span>Khách còn nợ:</span> <b id="sum-debt">0 đ</b>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="total_product_amount" id="total_product_amount" value="0">
                    <input type="hidden" name="total_final_amount" id="total_final_amount" value="0">
                    <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">XÁC NHẬN XUẤT ĐƠN</button>
                </div>
            </div>
        </div>
    </div>
</form>

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

<!-- MODAL THÊM KHÁCH HÀNG -->
<div class="modal fade" id="modalAddCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm Khách Hàng Mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formQuickAddCustomer">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên khách hàng *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-block">Lưu và Chọn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM ĐƠN VỊ VẬN CHUYỂN -->
<div class="modal fade" id="modalAddShipping" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-truck"></i> Thêm Đơn Vị Vận Chuyển</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formQuickAddShipping">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên đơn vị *</label>
                        <input type="text" name="name" class="form-control" required placeholder="GHTK, Viettel Post...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-block">Lưu đơn vị</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL THÊM NHANH SẢN PHẨM -->
<x-modal-quick-add-product />

@push('scripts')
<script>
    let rowIdx = 0;

    // 1. Hàm thêm dòng vào bảng (Đưa ra ngoài cùng để Modal Quick Add có thể gọi tới)
    function addProductToTable(data) {
        // Kiểm tra xem dữ liệu có hợp lệ không
        if (!data || !data.id) return;

        // 1. Kiểm tra trùng sản phẩm trong bảng
        if ($(`#row-${data.id}`).length > 0) {
            Swal.fire('Thông báo', 'Sản phẩm này đã có trong danh sách!', 'info');
            return;
        }

        // 2. Kiểm tra tồn kho (Nếu tồn = 0 thì không cho bán)
        if (parseInt(data.stock) <= 0) {
            Swal.fire('Cảnh báo', 'Sản phẩm này đã hết hàng!', 'error');
            return;
        }

        // 3. Ẩn dòng "Chưa có sản phẩm"
        $('#empty-row').hide();

        let displayName = data.name;
        if (data.variant_label) {
            displayName += ` - <span class="badge badge-secondary">${data.variant_label}</span>`;
        }

        let html = `
            <tr id="row-${data.id}">
                <td>
                    <b class="text-primary">${data.sku}</b> - ${displayName}
                    <input type="hidden" name="items[${rowIdx}][product_id]" value="${data.id}">
                </td>
                <td class="text-center text-bold">${data.stock}</td>
                <td>
                    <input type="number" name="items[${rowIdx}][quantity]" class="form-control form-control-sm qty text-center" value="1" min="1" max="${data.stock}">
                </td>
                <td>
                    <input type="number" name="items[${rowIdx}][unit_price]" class="form-control form-control-sm price text-right" value="${data.retail_price || 0}">
                </td>
                <td class="text-right subtotal font-weight-bold text-danger">0 đ</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fas fa-times"></i></button>
                </td>
            </tr>
        `;

        $('#order-body').append(html);
        rowIdx++;
        calculateAll();
    }

    // 2. Hàm tính toán tiền (Gộp chung logic)
    function calculateAll() {
        let totalProd = 0;

        $('#order-body tr').each(function() {
            if ($(this).attr('id') === 'empty-row') return;

            let q = parseFloat($(this).find('.qty').val()) || 0;
            let p = parseFloat($(this).find('.price').val()) || 0;
            let sub = q * p;

            $(this).find('.subtotal').text(new Intl.NumberFormat('vi-VN').format(sub) + ' đ');
            totalProd += sub;
        });

        let shipFee = parseFloat($('#shipping_fee').val()) || 0;
        let payor = $('#shipping_payor').val();

        // Nếu khách trả ship thì cộng vào tổng, shop trả thì không
        let totalFinal = totalProd + (payor === 'customer' ? shipFee : 0);
        let paid = parseFloat($('#paid_amount').val()) || 0;
        let debt = totalFinal - paid;

        // Cập nhật giao diện
        $('#sum-product').text(new Intl.NumberFormat('vi-VN').format(totalProd) + ' đ');
        $('#sum-ship').text(new Intl.NumberFormat('vi-VN').format(shipFee) + ' đ');
        $('#sum-final').text(new Intl.NumberFormat('vi-VN').format(totalFinal) + ' đ');
        $('#sum-debt').text(new Intl.NumberFormat('vi-VN').format(debt > 0 ? debt : 0) + ' đ');

        // Gán vào input ẩn để gửi lên server
        $('#total_product_amount').val(totalProd);
        $('#total_final_amount').val(totalFinal);
    }

    $(document).ready(function() {
        // 3. Sự kiện khi chọn sản phẩm từ ô Search Ajax (Component)
        $('#product_search').on('select2:select', function(e) {
            addProductToTable(e.params.data);
            $(this).val(null).trigger('change'); // Reset ô search
        });

        // 4. Sự kiện Xóa dòng
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            if ($('#order-body tr:visible').not('#empty-row').length === 0) {
                $('#empty-row').show();
            }
            calculateAll();
        });

        // 5. Sự kiện thay đổi số lượng, giá, phí ship
        $(document).on('input', '.qty, .price, #shipping_fee, #paid_amount, #shipping_payor', function() {
            calculateAll();
        });

        // 6. Modal Thêm tài khoản nhanh
        $('#quickAddAccountForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('accounts.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    let formatted = new Intl.NumberFormat('vi-VN').format(res.data.current_balance);
                    let newOpt = new Option(res.data.name + " (" + formatted + "đ)", res.data.id, true, true);
                    $('#account_id').append(newOpt).trigger('change');
                    $('#modalAddAccount').modal('hide');
                }
            });
        });

        // 7. Modal Thêm Khách hàng nhanh
        $('#formQuickAddCustomer').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    let newOpt = new Option(res.data.name + ' - ' + res.data.phone, res.data.id, true, true);
                    $('#customer_id').append(newOpt).trigger('change');
                    $('#modalAddCustomer').modal('hide');
                }
            });
        });

        // 8. Modal Thêm Vận chuyển nhanh
        $('#formQuickAddShipping').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('shipping_units.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    $('#shipping_unit_id').append(`<option value="${res.data.id}" selected>${res.data.name}</option>`);
                    $('#modalAddShipping').modal('hide');
                }
            });
        });
    });
</script>
@endpush
@endsection