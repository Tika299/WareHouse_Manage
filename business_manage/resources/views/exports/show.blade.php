@extends('layouts.app')
@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<style>
    .print-only {
        display: none;
    }

    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    @media print {

        html,
        body {
            background: #ffffff !important;
            font-size: 12px !important;
            color: #111 !important;
        }

        /* Ẩn toàn bộ thành phần admin khi in */
        .main-header,
        .main-sidebar,
        .content-header,
        .navbar,
        .nav,
        .nav-tabs,
        .breadcrumb,
        .btn,
        .no-print,
        .internal-only {
            display: none !important;
        }

        /* Xóa nền layout admin */
        .content-wrapper,
        .container-fluid,
        .container,
        .row,
        .col-12 {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }

        .invoice {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #ffffff !important;
        }

        .print-only {
            display: block !important;
        }

        .screen-title {
            display: none !important;
        }

        .print-header {
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .print-shop-name {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .print-shop-info {
            font-size: 12px;
            color: #444;
            line-height: 1.4;
        }

        .print-invoice-title {
            text-align: right;
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .print-order-code {
            text-align: right;
            font-size: 13px;
            color: #555;
        }

        .invoice-info {
            border: 1px solid #ddd;
            padding: 10px 8px;
            margin-top: 10px !important;
            margin-bottom: 14px;
        }

        .invoice-col {
            font-size: 12px;
            line-height: 1.5;
        }

        .invoice-col strong {
            font-size: 12px;
            text-transform: uppercase;
            color: #111 !important;
        }

        address {
            margin-bottom: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 10px !important;
        }

        table th,
        table td {
            border: 1px solid #ddd !important;
            padding: 7px 8px !important;
            vertical-align: middle !important;
        }

        table th {
            background: #f5f5f5 !important;
            font-weight: 700 !important;
            color: #111 !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background: #ffffff !important;
        }

        .summary-table th,
        .summary-table td {
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #ddd !important;
            padding: 7px 0 !important;
        }

        .total-row th,
        .total-row td {
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #111 !important;
            border-bottom: 2px solid #222 !important;
        }

        .note-box {
            min-height: 52px;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 4px;
            color: #444;
        }

        .print-footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px dashed #aaa;
            font-size: 12px;
            color: #444;
        }

        .signature-box {
            margin-top: 20px;
            text-align: center;
            font-weight: 600;
        }

        .signature-space {
            height: 55px;
        }
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Thẻ thông tin chung -->
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <div class="row">
                    <div class="col-12">
                        <div class="print-only print-header">
                            <div class="row">
                                <div class="col-6">
                                    <div class="print-shop-name">Marais de France</div>
                                    <div class="print-shop-info">
                                        Nước hoa & mỹ phẩm chính hãng<br>
                                        Hotline: 0795 891 525<br>
                                        Website: mfparis.vn
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="print-invoice-title">Phiếu bán hàng</div>
                                    <div class="print-order-code">
                                        Mã đơn: #DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}<br>
                                        Ngày tạo: {{ $order->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h4 class="screen-title">
                            <i class="fas fa-file-invoice text-success"></i> Đơn hàng: #DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            <small class="float-right text-muted">Ngày tạo: {{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </h4>
                    </div>
                </div>

                <div class="row invoice-info mt-3">
                    <div class="col-sm-4 invoice-col border-right">
                        <strong>KHÁCH HÀNG</strong>
                        <address>
                            <b class="text-primary">{{ $order->customer->name }}</b><br>
                            SĐT: {{ $order->customer->phone }}<br>
                            Địa chỉ: {{ $order->customer->address }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col border-right pl-4">
                        <strong>VẬN CHUYỂN</strong>
                        <address>
                            Đơn vị: {{ $order->shippingUnit->name ?? 'Tự giao' }}<br>
                            Phí ship: {{ number_format($order->shipping_fee) }} đ<br>
                            Người trả: {{ $order->shipping_payor == 'customer' ? 'Khách hàng' : 'Shop (Doanh nghiệp)' }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col pl-4">
                        <strong>THANH TOÁN</strong><br>
                        Trạng thái:
                        @if($order->paid_amount >= $order->total_final_amount)
                        <span class="badge badge-success">Đã trả đủ</span>
                        @else
                        <span class="badge badge-warning">Còn nợ: {{ number_format($order->total_final_amount - $order->paid_amount) }} đ</span>
                        @endif
                        <br>
                        Đã thu: {{ number_format($order->paid_amount) }} đ
                    </div>
                </div>

                <!-- Bảng danh sách sản phẩm & Lợi nhuận -->
                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Giá bán</th>
                                    <th class="text-right text-muted internal-only">Giá vốn (chốt)</th>
                                    <th class="text-right">Thành tiền</th>
                                    <th class="text-right text-success internal-only">Lợi nhuận</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalProfit = 0; @endphp

                                @foreach($order->details as $item)
                                @php
                                $lineProfit = ($item->unit_price - $item->cost_price_at_sale) * $item->quantity;
                                $totalProfit += $lineProfit;
                                @endphp

                                <tr>
                                    <td>{{ $item->product->name }}</td>

                                    <td class="text-center">
                                        {{ $item->quantity }}
                                    </td>

                                    <td class="text-right">
                                        {{ number_format($item->unit_price) }} đ
                                    </td>

                                    <td class="text-right text-muted internal-only">
                                        {{ number_format($item->cost_price_at_sale) }} đ
                                    </td>

                                    <td class="text-right font-weight-bold">
                                        {{ number_format($item->unit_price * $item->quantity) }} đ
                                    </td>

                                    <td class="text-right text-success font-weight-bold internal-only">
                                        + {{ number_format($lineProfit) }} đ
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mt-4">
                        <p class="lead">Ghi chú đơn hàng:</p>
                        <div class="note-box">
                            {{ $order->note ?? 'Không có ghi chú.' }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <table class="table summary-table">
                                <tr>
                                    <th style="width:50%">Tiền hàng:</th>
                                    <td class="text-right">{{ number_format($order->total_product_amount) }} đ</td>
                                </tr>
                                <tr>
                                    <th>Phí vận chuyển:</th>
                                    <td class="text-right">{{ number_format($order->shipping_fee) }} đ</td>
                                </tr>
                                <tr class="total-row">
                                    <th>TỔNG CỘNG:</th>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($order->total_final_amount) }} đ</td>
                                </tr>
                                @if($order->shipping_payor == 'shop')
                                <tr class="text-success h5 internal-only">
                                    <th>LÃI GỘP ĐƠN:</th>
                                    <td class="text-right font-weight-bold">{{ number_format($totalProfit - $order->shipping_fee) }} đ</td>
                                </tr>
                                @else
                                <tr class="text-success h5 internal-only">
                                    <th>LÃI GỘP ĐƠN:</th>
                                    <td class="text-right font-weight-bold">{{ number_format($totalProfit) }} đ</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="print-only print-footer">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="signature-box">
                                Người mua hàng
                                <div class="signature-space"></div>
                                <span>Ký, ghi rõ họ tên</span>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="signature-box">
                                Người bán hàng
                                <div class="signature-space"></div>
                                <span>Ký, ghi rõ họ tên</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row no-print mt-3">
                    <div class="col-12 text-right">
                        <a href="{{ route('exports.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Quay lại</a>
                        <button onclick="window.print()" class="btn btn-primary ml-2"><i class="fas fa-print"></i> In hóa đơn</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection