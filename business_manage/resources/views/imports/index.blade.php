@extends('layouts.app')

@section('title', 'Danh sách Phiếu nhập hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Danh sách Phiếu nhập hàng</h3>
        <!-- Nút tạo phiếu mới trỏ đúng về Route create -->
        <a href="{{ route('imports.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tạo phiếu nhập mới
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr class="text-13 bg-light">
                    <th>Mã phiếu</th>
                    <th>Ngày nhập</th>
                    <th>Nhà cung cấp</th>
                    <th class="text-right">Tiền hàng</th>
                    <th class="text-right">CP Phát sinh</th>
                    <th class="text-right">Tổng thanh toán</th>
                    <th class="text-right">Đã trả</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @forelse($orders as $order)
                <tr>
                    <td class="font-weight-bold">#PN{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->supplier->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($order->total_product_value) }} đ</td>
                    <td class="text-right text-primary">+{{ number_format($order->extra_cost) }} đ</td>
                    <td class="text-right font-weight-bold">{{ number_format($order->total_final_amount) }} đ</td>
                    <td class="text-right text-success">{{ number_format($order->paid_amount) }} đ</td>
                    <td class="text-center">
                        @if($order->paid_amount >= $order->total_final_amount)
                            <span class="badge badge-success">Đã thanh toán</span>
                        @else
                            <span class="badge badge-warning">Nợ: {{ number_format($order->total_final_amount - $order->paid_amount) }} đ</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{-- Nút xem chi tiết phiếu để xem giá vốn sau phân bổ của từng món --}}
                        <button class="btn btn-xs btn-default" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted p-4">Chưa có phiếu nhập hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{-- Phân trang --}}
        <div class="float-right">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection