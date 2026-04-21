@extends('layouts.app')
@section('title', 'Nghiệp vụ Đổi hàng (Multi-items)')

@section('content')
<form action="{{ route('exports.storeBarter') }}" method="POST" id="barterForm">
    @csrf
    <div class="row">
        <!-- 1. BÊN TRÁI: HÀNG MÌNH XUẤT ĐI -->
        <div class="col-md-6">
            <div class="card card-outline card-danger shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-danger">1. HÀNG MÌNH XUẤT ĐI</h3>
                    <div style="width: 200px;" class="ml-auto">
                        <x-select2-ajax
                            name="search_export" id="search_export" label=""
                            :url="route('products.searchAjax')" placeholder="Tìm hàng xuất..." />
                    </div>
                    <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#quickAddProductModal">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="table-export">
                        <thead class="bg-light text-13">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="100">SL</th>
                                <th width="120">Giá bán</th>
                                <th width="30">#</th>
                            </tr>
                        </thead>
                        <tbody id="body-export">
                            <tr class="empty-row">
                                <td colspan="4" class="text-center p-3 text-muted">Chưa có hàng xuất</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. BÊN PHẢI: HÀNG KHÁCH ĐƯA TỚI -->
        <div class="col-md-6">
            <div class="card card-outline card-success shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-success">2. HÀNG THU MUA (CỦA KHÁCH)</h3>
                    <div style="width: 200px;" class="ml-auto">
                        <x-select2-ajax
                            name="search_import" id="search_import" label=""
                            :url="route('products.searchAjax')" placeholder="Tìm hàng thu..." />
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" id="table-import">
                        <thead class="bg-light text-13">
                            <tr>
                                <th>Sản phẩm thu</th>
                                <th width="100">SL</th>
                                <th width="120">Giá thu</th>
                                <th width="30">#</th>
                            </tr>
                        </thead>
                        <tbody id="body-import">
                            <tr class="empty-row">
                                <td colspan="4" class="text-center p-3 text-muted">Chưa có hàng thu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TỔNG KẾT & THANH TOÁN -->
    <div class="card shadow-lg border-warning">
        <div class="card-body">
            <div class="row align-items-center text-center">
                <div class="col-md-3">
                    <label class="text-muted">TỔNG XUẤT</label>
                    <h3 id="txt-total-exp" class="text-danger">0 đ</h3>
                </div>
                <div class="col-md-1"><i class="fas fa-minus fa-2x text-muted mt-2"></i></div>
                <div class="col-md-3">
                    <label class="text-muted">TỔNG THU</label>
                    <h3 id="txt-total-imp" class="text-success">0 đ</h3>
                </div>
                <div class="col-md-1"><i class="fas fa-equals fa-2x text-muted mt-2"></i></div>
                <div class="col-md-4 bg-light border rounded py-2">
                    <label class="text-dark font-weight-bold">CHÊNH LỆCH (KHÁCH CẦN TRẢ THÊM)</label>
                    <h2 id="txt-diff" class="text-primary font-weight-bold mb-0">0 đ</h2>
                </div>
            </div>
            <hr>
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label>Khách hàng <span class="text-danger">*</span></label>
                    <div class="input-group" style="flex-wrap: nowrap;">
                        <x-select2-ajax
                            name="customer_id" id="customer_id" label=""
                            :url="route('customers.searchAjax')" placeholder="Tìm khách..." required="true" />
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddCustomer">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>Khách đưa thêm tiền</label>
                    <input type="number" name="paid_amount" id="in-paid" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label>Tài khoản tiền <span class="text-danger">*</span></label>
                    <div class="input-group" style="flex-wrap: nowrap;">
                        <select name="account_id" class="form-control" required>
                            @foreach($accounts as $a) <option value="{{$a->id}}">{{$a->name}}</option> @endforeach
                        </select>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddAccount">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label>Vận chuyển <span class="text-danger">*</span></label>
                    <div class="input-group" style="flex-wrap: nowrap;">
                        <select name="shipping_unit_id" class="form-control" required>
                            @foreach($shippingUnits as $su) <option value="{{$su->id}}">{{$su->name}}</option> @endforeach
                        </select>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalAddShipping">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-warning btn-block btn-lg font-weight-bold">
                        <i class="fas fa-sync"></i> XÁC NHẬN ĐỔI HÀNG
                    </button>
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

<x-modal-quick-add-product />

@push('scripts')
<script>
    let expIdx = 0;
    let impIdx = 0;

    $(document).ready(function() {
        // --- 1. CHỌN HÀNG XUẤT ---
        $('#search_export').on('select2:select', function(e) {
            let data = e.params.data;
            $('#body-export .empty-row').hide();
            let html = `
                <tr>
                    <td><b>${data.sku}</b> - ${data.name}<input type="hidden" name="export_items[${expIdx}][product_id]" value="${data.id}"></td>
                    <td><input type="number" name="export_items[${expIdx}][quantity]" class="form-control form-control-sm exp-qty" value="1" min="1"></td>
                    <td><input type="number" name="export_items[${expIdx}][unit_price]" class="form-control form-control-sm exp-price" value="${data.retail_price}"></td>
                    <td><button type="button" class="btn btn-xs btn-danger btn-remove"><i class="fas fa-times"></i></button></td>
                </tr>`;
            $('#body-export').append(html);
            expIdx++;
            $(this).val(null).trigger('change');
            calculateBarter();
        });

        // --- 2. CHỌN HÀNG THU ---
        $('#search_import').on('select2:select', function(e) {
            let data = e.params.data;
            $('#body-import .empty-row').hide();
            let html = `
                <tr>
                    <td><b>${data.sku}</b> - ${data.name}<input type="hidden" name="import_items[${impIdx}][product_id]" value="${data.id}"></td>
                    <td><input type="number" name="import_items[${impIdx}][quantity]" class="form-control imp-qty" value="1" min="1"></td>
                    <td><input type="number" name="import_items[${impIdx}][buyback_price]" class="form-control imp-price" value="${data.cost_price}"></td>
                    <td><button type="button" class="btn btn-xs btn-danger btn-remove"><i class="fas fa-times"></i></button></td>
                </tr>`;
            $('#body-import').append(html);
            impIdx++;
            $(this).val(null).trigger('change');
            calculateBarter();
        });

        // --- 3. XÓA DÒNG ---
        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
            calculateBarter();
        });

        // --- 4. TÍNH TOÁN ---
        $(document).on('input', '.exp-qty, .exp-price, .imp-qty, .imp-price, #in-paid', function() {
            calculateBarter();
        });

        function calculateBarter() {
            let totalExp = 0;
            $('#body-export tr').each(function() {
                let q = $(this).find('.exp-qty').val() || 0;
                let p = $(this).find('.exp-price').val() || 0;
                totalExp += (q * p);
            });

            let totalImp = 0;
            $('#body-import tr').each(function() {
                let q = $(this).find('.imp-qty').val() || 0;
                let p = $(this).find('.imp-price').val() || 0;
                totalImp += (q * p);
            });

            let paid = parseFloat($('#in-paid').val()) || 0;
            let diff = totalExp - totalImp;

            $('#txt-total-exp').text(new Intl.NumberFormat('vi-VN').format(totalExp) + ' đ');
            $('#txt-total-imp').text(new Intl.NumberFormat('vi-VN').format(totalImp) + ' đ');
            $('#txt-diff').text(new Intl.NumberFormat('vi-VN').format(diff) + ' đ');
        }
    });

    // Hàm nhận dữ liệu từ Modal Thêm Nhanh (Sử dụng cho hàng Xuất)
    function addProductToTable(data) {
        // Tự động bắn vào bảng hàng Xuất vì đây là hàng mới tạo
        $('#body-export .empty-row').hide();
        let html = `
            <tr>
                <td><b>${data.sku}</b> - ${data.name}<input type="hidden" name="export_items[${expIdx}][product_id]" value="${data.id}"></td>
                <td><input type="number" name="export_items[${expIdx}][quantity]" class="form-control form-control-sm exp-qty" value="1" min="1"></td>
                <td><input type="number" name="export_items[${expIdx}][unit_price]" class="form-control form-control-sm exp-price" value="${data.retail_price}"></td>
                <td><button type="button" class="btn btn-xs btn-danger btn-remove"><i class="fas fa-times"></i></button></td>
            </tr>`;
        $('#body-export').append(html);
        expIdx++;
        calculateBarter();
    }
</script>
@endpush
@endsection