<!-- resources/views/components/modal-quick-add-product.blade.php -->
<div class="modal fade" id="quickAddProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-box"></i> Thêm nhanh sản phẩm mới</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickAddProductForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mã SKU/Barcode <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" placeholder="Ví dụ: SP001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Nhập tên sản phẩm..." required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá vốn (BQGQ)</label>
                            <input type="number" name="cost_price" class="form-control" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Đơn vị tính</label>
                            <input type="text" name="unit" class="form-control" value="Cái">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tồn kho ban đầu</label>
                            <input type="number" name="stock_quantity" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btnSaveQuickProduct">Lưu và Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    $(document).ready(function() {
        $('#quickAddProductForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#btnSaveQuickProduct');
            btn.prop('disabled', true).text('Đang xử lý...');

            $.ajax({
                url: "{{ route('products.store') }}",
                method: "POST",
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#quickAddProductModal').modal('hide');
                    $('#quickAddProductForm')[0].reset();
                    
                    // Xử lý backdrop nếu bị kẹt
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');

                    Swal.fire('Thành công', 'Đã tạo sản phẩm mới!', 'success');
                },
                error: function(xhr) {
                    let error = xhr.responseJSON.message || 'Lỗi: Mã SKU có thể đã tồn tại.';
                    Swal.fire('Thất bại', error, 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Lưu và Thêm');
                }
            });
        });
    });
</script>
@endpush
@endonce