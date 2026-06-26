@extends('layouts.app')
@section('title', 'Chi tiết phiếu hoàn trả #' . $return->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-undo-alt text-danger"></i> PHIẾU HOÀN TRẢ NHÀ CUNG CẤP
                            <small class="float-right text-muted">Ngày hoàn trả: {{ optional($return->returned_at)->format('d/m/Y H:i') }}</small>
                        </h4>
                    </div>
                </div>

                <div class="row invoice-info mt-3">
                    <div class="col-sm-4 invoice-col border-right">
                        <strong>NHÀ CUNG CẤP</strong>
                        <address>
                            <b class="text-primary">{{ $return->supplier->name ?? '-' }}</b><br>
                            SĐT: {{ $return->supplier->phone ?? '-' }}<br>
                            Địa chỉ: {{ $return->supplier->address ?? '-' }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col border-right pl-4">
                        <strong>THÔNG TIN XỬ LÝ</strong>
                        <address>
                            Phiếu nhập gốc: <b>#PN{{ str_pad($return->purchase_order_id, 5, '0', STR_PAD_LEFT) }}</b><br>
                            Trạng thái: <b class="text-success">{{ $return->status }}</b><br>
                            Giá trị hoàn trả: <b class="text-danger">{{ number_format($return->total_return_value) }} đ</b>
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col pl-4">
                        <strong>MÃ PHIẾU: {{ $return->return_code }}</strong><br><br>
                        <b>Ngày lập:</b> {{ $return->created_at->format('d/m/Y H:i') }}<br>
                        <b>Ghi chú:</b> {{ $return->note ?? 'Không có' }}
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light text-13">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Số lượng trả</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-right">Thành tiền</th>
                                    <th>Lý do</th>
                                </tr>
                            </thead>
                            <tbody class="text-13">
                                @foreach($return->details as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <b>{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</b><br>
                                        <small class="text-muted">SKU: {{ $item->product->sku ?? '---' }}</small>
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                    <td class="text-right">{{ number_format($item->return_price) }} đ</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->return_value) }} đ</td>
                                    <td>{{ $item->reason ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-6">
                        <p class="lead">Ghi chú:</p>
                        <div class="text-muted well well-sm shadow-none p-2 border rounded" style="min-height: 100px;">
                            {{ $return->note ?? 'Không có ghi chú.' }}
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

                <div class="row no-print mt-5 pt-3 border-top">
                    <div class="col-12 text-right">
                        <a href="{{ route('purchase-returns.create', ['purchase_order_id' => $return->purchase_order_id]) }}" class="btn btn-warning mr-2">
                            <i class="fas fa-undo-alt"></i> Tạo phiếu hoàn trả mới
                        </a>
                        <a href="{{ route('purchase-returns.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <button onclick="window.print()" class="btn btn-primary ml-2">
                            <i class="fas fa-print"></i> In phiếu hoàn trả
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
