@extends('layouts.app')

@section('title', 'Chi tiết phiếu nhập #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <!-- Tiêu đề -->
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-file-import text-primary"></i> PHIẾU NHẬP HÀNG
                            <small class="float-right text-muted">Ngày nhập: {{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </h4>
                    </div>
                </div>

                <!-- Thông tin chung -->
                <div class="row invoice-info mt-3">
                    <div class="col-sm-4 invoice-col border-right">
                        <strong>NHÀ CUNG CẤP</strong>
                        <address>
                            <b class="text-primary">{{ $order->supplier->name }}</b><br>
                            SĐT: {{ $order->supplier->phone }}<br>
                            Địa chỉ: {{ $order->supplier->address }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col border-right pl-4">
                        <strong>THANH TOÁN</strong>
                        <address>
                            Tài khoản chi: {{ $order->account->name ?? 'N/A' }}<br>
                            Đã trả NCC: <b class="text-success">{{ number_format($order->paid_amount) }} đ</b><br>
                            Còn nợ: <b class="text-danger">{{ number_format($order->total_final_amount - $order->paid_amount) }} đ</b>
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col pl-4">
                        <strong>MÃ PHIẾU: #PN{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong><br>
                        <br>
                        <b>Tổng tiền hàng:</b> {{ number_format($order->total_product_value) }} đ<br>
                        <b>Phí phát sinh:</b> <span class="text-primary">+{{ number_format($order->extra_cost) }} đ</span><br>
                        <b class="h4 text-danger">TỔNG CỘNG: {{ number_format($order->total_final_amount) }} đ</b>
                    </div>
                </div>

                <!-- Bảng chi tiết sản phẩm -->
                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light text-13">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Giá nhập (NCC)</th>
                                    <th class="text-right text-primary">Phí phân bổ</th>
                                    <th class="text-right text-danger">Giá nhập thực tế</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="text-13">
                                @foreach($order->details as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <b>{{ $item->product->name }}</b><br>
                                        <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->import_price) }} đ</td>
                                    <td class="text-right text-primary">+{{ number_format($item->allocated_cost) }} đ</td>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($item->final_unit_cost) }} đ</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->quantity * $item->import_price) }} đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Chữ ký & Ghi chú -->
                <div class="row mt-4">
                    <div class="col-6">
                        <p class="lead">Ghi chú:</p>
                        <div class="text-muted well well-sm shadow-none p-2 border rounded" style="min-height: 100px;">
                            {{ $order->note ?? 'Không có ghi chú.' }}
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="row" style="margin-top: 50px;">
                            <div class="col-6">
                                <b>Người lập phiếu</b><br><br><br>
                                <i>(Ký và ghi rõ họ tên)</i>
                            </div>
                            <div class="col-6">
                                <b>Thủ kho</b><br><br><br>
                                <i>(Ký và ghi rõ họ tên)</i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nút thao tác -->
                <div class="row no-print mt-5 pt-3 border-top">
                    <div class="col-12 text-right">
                        <a href="{{ route('imports.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <button onclick="window.print()" class="btn btn-primary ml-2">
                            <i class="fas fa-print"></i> In phiếu nhập kho
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .content-wrapper {
            background: white !important;
            padding: 0 !important;
        }

        .main-header,
        .main-footer,
        .no-print,
        .bg-light {
            display: none !important;
        }

        .invoice {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }
    }
</style>
@endsection