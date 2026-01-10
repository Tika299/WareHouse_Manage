@extends('layouts.app')
@section('content')
<div class="card card-outline card-danger">
    <div class="card-header"><h3 class="card-title font-weight-bold">Lập Phiếu Xuất Kho Nội Bộ</h3></div>
    <form action="{{ route('internal_exports.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Lý do xuất kho <span class="text-danger">*</span></label>
                    <select name="reason_type" class="form-control" required>
                        <option value="Người nhà dùng">Người nhà dùng</option>
                        <option value="Dùng cho kinh doanh">Dùng cho mục đích kinh doanh</option>
                        <option value="Hàng biếu tặng">Hàng biếu tặng</option>
                        <option value="Hàng hư hỏng/Hết hạn">Hàng hư hỏng / Hết hạn</option>
                        <option value="Khác">Lý do khác</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label>Ghi chú chi tiết</label>
                    <input type="text" name="note" class="form-control" placeholder="Ví dụ: Xuất máy in dùng cho văn phòng kế toán...">
                </div>
            </div>

            <table class="table table-bordered mt-4" id="itemTable">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150">Tồn kho</th>
                        <th width="150">Số lượng xuất</th>
                        <th width="100"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-control">
                                @foreach($products as $p)
                                <option value="{{ $p->id }}"> {{ $p->sku }} - {{ $p->name }} (Vốn: {{ number_format($p->cost_price) }}đ)</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">-</td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-info" id="btnAddItem">+ Thêm sản phẩm</button>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-danger px-5">XÁC NHẬN XUẤT KHO</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let rowIdx = 1;
    $('#btnAddItem').click(function() {
        let html = `<tr>
            <td><select name="items[${rowIdx}][product_id]" class="form-control">
                @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
            </select></td>
            <td></td>
            <td><input type="number" name="items[${rowIdx}][quantity]" class="form-control" value="1" min="1"></td>
            <td><button type="button" class="btn btn-danger btn-xs btnRemove">x</button></td>
        </tr>`;
        $('#itemTable tbody').append(html);
        rowIdx++;
    });
    $(document).on('click', '.btnRemove', function() { $(this).closest('tr').remove(); });
</script>
@endpush
@endsection