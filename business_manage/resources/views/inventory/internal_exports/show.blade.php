@extends('layouts.app')

@section('title', 'Chi tiết phiếu xuất nội bộ #' . $export->id)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="invoice p-3 mb-3 shadow-sm rounded">
                <!-- Tiêu đề phiếu -->
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-file-export text-danger"></i> PHIẾU XUẤT KHO NỘI BỘ
                            <small class="float-right text-muted">Ngày lập: {{ $export->created_at->format('d/m/Y H:i') }}</small>
                        </h4>
                    </div>
                </div>

                <!-- Thông tin chung -->
                <div class="row invoice-info mt-3">
                    <div class="col-sm-4 invoice-col border-right">
                        <strong>THÔNG TIN PHIẾU</strong>
                        <address>
                            Mã phiếu: <b class="text-danger">#IE{{ str_pad($export->id, 5, '0', STR_PAD_LEFT) }}</b><br>
                            Người lập phiếu: {{ $export->user->name }}<br>
                            Trạng thái: <span class="badge badge-success">Đã xuất kho</span>
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col border-right pl-4">
                        <strong>LÝ DO XUẤT</strong>
                        <address>
                            Loại lý do: <b class="text-warning">{{ $export->reason_type }}</b><br>
                            Ghi chú: {{ $export->note ?? 'Không có ghi chú thêm.' }}
                        </address>
                    </div>
                    <div class="col-sm-4 invoice-col pl-4">
                        <strong>TỔNG GIÁ TRỊ VỐN</strong><br>
                        <h3 class="text-danger font-weight-bold">{{ number_format($export->total_cost_value) }} đ</h3>
                        <small class="text-muted">(Giá trị này được tính dựa trên giá vốn tại thời điểm xuất)</small>
                    </div>
                </div>

                <!-- Bảng danh sách sản phẩm -->
                <div class="row mt-4">
                    <div class="col-12 table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Mã SKU</th>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-center">Số lượng xuất</th>
                                    <th>Đơn vị</th>
                                    <th class="text-right">Đơn giá vốn</th>
                                    <th class="text-right">Thành tiền (Vốn)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($export->details as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->sku }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                    <td>{{ $item->product->unit ?? 'Cái' }}</td>
                                    <td class="text-right">{{ number_format($item->cost_price) }} đ</td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($item->quantity * $item->cost_price) }} đ
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">TỔNG CỘNG GIÁ TRỊ THIỆT HẠI VỐN:</th>
                                    <th class="text-right text-danger text-xl">
                                        {{ number_format($export->total_cost_value) }} đ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Chữ ký -->
                <div class="row mt-5">
                    <div class="col-4 text-center">
                        <p class="mb-5"><b>Người lập phiếu</b></p>
                        <br><br>
                        <i>(Ký và ghi rõ họ tên)</i>
                    </div>
                    <div class="col-4 text-center">
                        <p class="mb-5"><b>Người nhận hàng</b></p>
                        <br><br>
                        <i>(Ký và ghi rõ họ tên)</i>
                    </div>
                    <div class="col-4 text-center">
                        <p class="mb-5"><b>Thủ kho</b></p>
                        <br><br>
                        <i>(Ký và ghi rõ họ tên)</i>
                    </div>
                </div>

                <!-- Nút thao tác -->
                <div class="row no-print mt-5 pt-3 border-top">
                    <div class="col-12 text-right">
                        <a href="{{ route('internal_exports.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                        <button onclick="window.print()" class="btn btn-primary ml-2">
                            <i class="fas fa-print"></i> In phiếu xuất kho
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .content-wrapper { background: white !important; padding: 0 !important; }
        .main-header, .main-footer, .no-print { display: none !important; }
        .invoice { border: none !important; box-shadow: none !important; margin: 0 !important; padding: 0 !important; }
    }
</style>
@endsection