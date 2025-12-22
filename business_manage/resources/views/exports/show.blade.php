@extends('layouts.app')
@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Thẻ thông tin chung -->
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <div class="row">
                    <div class="col-12">
                        <h4>
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
                                    <th class="text-right text-muted">Giá vốn (chốt)</th>
                                    <th class="text-right">Thành tiền</th>
                                    <th class="text-right text-success">Lợi nhuận</th>
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
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price) }} đ</td>
                                        <td class="text-right text-muted italic">{{ number_format($item->cost_price_at_sale) }} đ</td>
                                        <td class="text-right font-weight-bold">{{ number_format($item->unit_price * $item->quantity) }} đ</td>
                                        <td class="text-right text-success font-weight-bold">+ {{ number_format($lineProfit) }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mt-4">
                        <p class="lead">Ghi chú đơn hàng:</p>
                        <div class="text-muted well well-sm shadow-none p-2 border rounded">
                            {{ $order->note ?? 'Không có ghi chú.' }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th style="width:50%">Tiền hàng:</th>
                                    <td class="text-right">{{ number_format($order->total_product_amount) }} đ</td>
                                </tr>
                                <tr>
                                    <th>Phí vận chuyển:</th>
                                    <td class="text-right">{{ number_format($order->shipping_fee) }} đ</td>
                                </tr>
                                <tr class="h4">
                                    <th>TỔNG CỘNG:</th>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($order->total_final_amount) }} đ</td>
                                </tr>
                                @if($order->shipping_payor == 'shop')
                                <tr class="text-success h5">
                                    <th>LÃI GỘP ĐƠN:</th>
                                    <td class="text-right font-weight-bold">{{ number_format($totalProfit - $order->shipping_fee) }} đ</td>
                                </tr>
                                @else
                                <tr class="text-success h5">
                                    <th>LÃI GỘP ĐƠN:</th>
                                    <td class="text-right font-weight-bold">{{ number_format($totalProfit) }} đ</td>
                                </tr>
                                @endif
                            </table>
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