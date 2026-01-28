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

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('exports.index') }}" method="GET">
            <div class="row">
                <!-- Tìm mã phiếu -->
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Mã phiếu (#PN...)" value="{{ request('search') }}">
                </div>

                <!-- Lọc Nhà cung cấp -->
                <div class="col-md-2">
                    <x-select2-ajax
                        name="customer_id"
                        label="" {{-- Để trống nhãn cho thanh lọc gọn gàng --}}
                        :url="route('customers.searchAjax')"
                        placeholder="-- Khách hàng --"
                        :value="request('customer_id')"
                        :text="$selectedCustomer ? $selectedCustomer->name : null" />
                </div>

                <!-- Lọc Trạng thái -->
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">-- Trạng thái --</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="debt" {{ request('status') == 'debt' ? 'selected' : '' }}>Còn nợ NCC</option>
                    </select>
                </div>

                <!-- Lọc Ngày -->
                <div class="col-md-3 d-flex">
                    <input type="date" name="start_date" class="form-control form-control-sm mr-1" value="{{ request('start_date') }}">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <!-- Nút bấm -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('exports.index') }}" class="btn btn-default btn-sm" title="Xóa lọc">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
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