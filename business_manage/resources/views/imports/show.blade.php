@extends('layouts.app')

@section('title', 'Chi tiáº¿t phiáº¿u nháº­p #' . $order->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <!-- TiÃªu Ä‘á» -->
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-file-import text-primary"></i> PHIáº¾U NHáº¬P HÃ€NG
                            <small class="float-right text-muted">NgÃ y nháº­p: {{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </h4>
                    </div>
                </div>

                <!-- ThÃ´ng tin chung -->
                <div class="row invoice-info mt-3">
                    <div class="col-sm-4 invoice-col border-right">
                        <strong>NHÃ€ CUNG Cáº¤P</strong>
                        <address>
                            <b class="text-primary">{{ $order->supplier->name }}</b><br>
                            SÄT: {{ $order->supplier->phone }}<br>
                            Äá»‹a chá»‰: {{ $order->supplier->address }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col border-right pl-4">
                        <strong>THANH TOÃN</strong>
                        <address>
                            TÃ i khoáº£n chi: {{ $order->account->name ?? 'N/A' }}<br>
                            ÄÃ£ tráº£ NCC: <b class="text-success">{{ number_format($order->paid_amount) }} Ä‘</b><br>
                            CÃ²n ná»£: <b class="text-danger">{{ number_format($order->total_final_amount - $order->paid_amount) }} Ä‘</b>
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col pl-4">
                        <strong>MÃƒ PHIáº¾U: #PN{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong><br>
                        <br>
                        <b>Tá»•ng tiá»n hÃ ng:</b> {{ number_format($order->total_product_value) }} Ä‘<br>
                        <b>PhÃ­ phÃ¡t sinh:</b> <span class="text-primary">+{{ number_format($order->extra_cost) }} Ä‘</span><br>
                        <b class="h4 text-danger">Tá»”NG Cá»˜NG: {{ number_format($order->total_final_amount) }} Ä‘</b>
                        <div class="mt-2">
                            <span class="badge badge-pill badge-light border text-muted px-3 py-2">
                                Đã có {{ $purchaseReturnCount ?? 0 }} phiếu hoàn trả
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Báº£ng chi tiáº¿t sáº£n pháº©m -->
                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light text-13">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Sáº£n pháº©m</th>
                                    <th class="text-center">Sá»‘ lÆ°á»£ng</th>
                                    <th class="text-right">GiÃ¡ nháº­p (NCC)</th>
                                    <th class="text-right text-primary">PhÃ­ phÃ¢n bá»•</th>
                                    <th class="text-right text-danger">GiÃ¡ nháº­p thá»±c táº¿</th>
                                    <th class="text-right">ThÃ nh tiá»n</th>
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
                                    <td class="text-right">{{ number_format($item->import_price) }} Ä‘</td>
                                    <td class="text-right text-primary">+{{ number_format($item->allocated_cost) }} Ä‘</td>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($item->final_unit_cost) }} Ä‘</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->quantity * $item->import_price) }} Ä‘</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Chá»¯ kÃ½ & Ghi chÃº -->
                <div class="row mt-4">
                    <div class="col-6">
                        <p class="lead">Ghi chÃº:</p>
                        <div class="text-muted well well-sm shadow-none p-2 border rounded" style="min-height: 100px;">
                            {{ $order->note ?? 'KhÃ´ng cÃ³ ghi chÃº.' }}
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="row" style="margin-top: 50px;">
                            <div class="col-6">
                                <b>NgÆ°á»i láº­p phiáº¿u</b><br><br><br>
                                <i>(KÃ½ vÃ  ghi rÃµ há» tÃªn)</i>
                            </div>
                            <div class="col-6">
                                <b>Thá»§ kho</b><br><br><br>
                                <i>(KÃ½ vÃ  ghi rÃµ há» tÃªn)</i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NÃºt thao tÃ¡c -->
                <div class="row no-print mt-5 pt-3 border-top">
                    <div class="col-12 text-right">
                        <a href="{{ route('purchase-returns.index', ['purchase_order_id' => $order->id]) }}" class="btn btn-light border text-secondary mr-2">
                            <i class="fas fa-history"></i> Xem phiáº¿u hoÃ n tráº£
                        </a>
                        <a href="{{ route('purchase-returns.create', ['purchase_order_id' => $order->id]) }}" class="btn btn-danger mr-2">
                            <i class="fas fa-undo-alt"></i> HoÃ n tráº£ NCC
                        </a>
                        <a href="{{ route('imports.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay láº¡i danh sÃ¡ch
                        </a>
                        <button onclick="window.print()" class="btn btn-primary ml-2">
                            <i class="fas fa-print"></i> In phiáº¿u nháº­p kho
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
