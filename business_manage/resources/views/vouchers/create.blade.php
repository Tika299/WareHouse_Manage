@extends('layouts.app')
@section('title', 'Lập phiếu Thu - Chi')

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <form action="{{ route('vouchers.store') }}" method="POST">
            @csrf
            <div class="card card-outline card-success shadow">
                <div class="card-header"><h3 class="card-title font-weight-bold">Lập phiếu tài chính</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Loại giao dịch</label>
                        <select name="voucher_type" id="voucher_type" class="form-control form-control-lg text-bold">
                            <option value="receipt" class="text-success">THU TIỀN (Tiền vào quỹ)</option>
                            <option value="payment" class="text-danger">CHI TIỀN (Tiền ra khỏi quỹ)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hạng mục</label>
                        <select name="category" id="category" class="form-control" required>
                            <option value="debt_customer">Thu nợ gộp từ Khách hàng</option>
                            <option value="debt_supplier">Chi trả nợ cho Nhà cung cấp</option>
                            <option value="operational">Chi phí vận hành (Điện, nước, lương...)</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <!-- Khu vực Khách hàng -->
                    <div id="div_customer" class="form-group">
                        <label>Chọn Khách hàng trả nợ</label>
                        <select name="customer_id" class="form-control select2">
                            <option value="">-- Chọn khách hàng --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $selected_customer == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} (Nợ: {{ number_format($c->total_debt) }}đ)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Khu vực NCC -->
                    <div id="div_supplier" class="form-group" style="display:none">
                        <label>Chọn Nhà cung cấp để trả nợ</label>
                        <select name="supplier_id" class="form-control select2">
                            <option value="">-- Chọn NCC --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ $selected_supplier == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} (Mình nợ: {{ number_format($s->total_debt) }}đ)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Tài khoản tiền</label>
                            <select name="account_id" class="form-control">
                                @foreach($accounts as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }} (Dư: {{ number_format($a->current_balance) }}đ)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Số tiền giao dịch</label>
                            <input type="number" name="amount" class="form-control form-control-lg text-danger" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú / Nội dung chi tiết</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Thanh toán tiền điện tháng 12..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block btn-lg"><b>XÁC NHẬN GIAO DỊCH</b></button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    $('#category').change(function() {
        let val = $(this).val();
        if(val === 'debt_customer') {
            $('#div_customer').show();
            $('#div_supplier').hide();
            $('#voucher_type').val('receipt');
        } else if(val === 'debt_supplier') {
            $('#div_customer').hide();
            $('#div_supplier').show();
            $('#voucher_type').val('payment');
        } else {
            $('#div_customer').hide();
            $('#div_supplier').hide();
        }
    });
    // Kích hoạt ngay khi load trang
    $('#category').trigger('change');
</script>
@endpush
@endsection