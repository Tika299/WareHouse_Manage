@extends('layouts.app')
@section('content')
<div class="card card-dark">
    <div class="card-header"><h3 class="card-title">Lập Phiếu Kiểm Kê Kho</h3></div>
    <form action="{{ route('audits.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Ghi chú kiểm kho</label>
                <input type="text" name="note" class="form-control" placeholder="Ví dụ: Kiểm kê định kỳ tháng 12">
            </div>

            <table class="table table-bordered" id="auditTable">
                <thead class="bg-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150">Tồn hệ thống</th>
                        <th width="200">Tồn thực tế</th>
                        <th width="150">Chênh lệch</th>
                        <th width="200">Giá trị lệch (Dựa trên giá vốn)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $p)
                    <tr>
                        <td>
                            {{ $p->name }}
                            <input type="hidden" name="items[{{$index}}][product_id]" value="{{ $p->id }}">
                        </td>
                        <td class="text-center system-qty">{{ $p->stock_quantity }}</td>
                        <td>
                            <input type="number" name="items[{{$index}}][actual_qty]" 
                                   class="form-control actual-qty" 
                                   value="{{ $p->stock_quantity }}"
                                   data-cost="{{ $p->cost_price }}">
                        </td>
                        <td class="text-center diff-qty">0</td>
                        <td class="text-right diff-value">0 đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <h5 class="mr-3">Tổng giá trị chênh lệch: <span id="total-diff-display" class="text-bold">0 đ</span></h5>
            <button type="submit" class="btn btn-dark px-5">XÁC NHẬN ĐIỀU CHỈNH KHO</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    $(document).on('input', '.actual-qty', function() {
        let tr = $(this).closest('tr');
        let systemQty = parseInt(tr.find('.system-qty').text());
        let actualQty = parseInt($(this).val()) || 0;
        let costPrice = parseFloat($(this).data('cost'));

        let diff = actualQty - systemQty;
        let diffValue = diff * costPrice;

        // Hiển thị chênh lệch
        tr.find('.diff-qty').text(diff).removeClass('text-danger text-success');
        if(diff < 0) tr.find('.diff-qty').addClass('text-danger');
        if(diff > 0) tr.find('.diff-qty').addClass('text-success');

        // Hiển thị giá trị lệch
        tr.find('.diff-value').text(new Intl.NumberFormat('vi-VN').format(diffValue) + ' đ');
        
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
        
        let color = total < 0 ? 'text-danger' : (total > 0 ? 'text-success' : '');
        $('#total-diff-display').text(new Intl.NumberFormat('vi-VN').format(total) + ' đ').removeClass('text-danger text-success').addClass(color);
    }
</script>
@endpush
@endsection