@extends('layouts.app')

@section('title', 'Danh sách Đơn hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Quản lý Đơn hàng / Xuất kho</h3>
        <!-- Nút tạo đơn trỏ về đúng route create -->
        <a href="{{ route('exports.create') }}" class="btn btn-success btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tạo đơn hàng mới
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="text-13 bg-light">
                    <th>Mã đơn</th>
                    <th>Ngày tạo</th>
                    <th>Khách hàng</th>
                    <th>ĐV Vận chuyển</th>
                    <th class="text-right">Phí Ship</th>
                    <th class="text-center">Người trả ship</th>
                    <th class="text-right">Tổng tiền đơn</th>
                    <th class="text-center">Thanh toán</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @forelse($orders as $order)
                <tr>
                    <td class="font-weight-bold">#DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('customers.show', $order->customer_id) }}">
                            {{ $order->customer->name ?? 'N/A' }}
                        </a>
                    </td>
                    <td>{{ $order->shippingUnit->name ?? 'Tự giao' }}</td>
                    <td class="text-right">{{ number_format($order->shipping_fee) }} đ</td>
                    <td class="text-center">
                        @if($order->shipping_payor == 'customer')
                            <span class="badge badge-info">Khách chịu</span>
                        @else
                            <span class="badge badge-secondary">Shop chịu</span>
                        @endif
                    </td>
                    <td class="text-right font-weight-bold">{{ number_format($order->total_final_amount) }} đ</td>
                    <td class="text-center">
                        @if($order->paid_amount >= $order->total_final_amount)
                            <span class="badge badge-success">Đã hoàn tất</span>
                        @elseif($order->paid_amount > 0)
                            <span class="badge badge-warning">Cọc: {{ number_format($order->paid_amount) }} đ</span>
                        @else
                            <span class="badge badge-danger">Chưa trả tiền</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{-- Nút xem chi tiết để xem giá vốn đã chốt và các sản phẩm trong đơn --}}
                        <a href="{{ route('exports.show', $order->id) }}" class="btn btn-xs btn-default" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-xs btn-default" title="In hóa đơn"><i class="fas fa-print"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted p-4">Chưa có đơn hàng nào được tạo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection