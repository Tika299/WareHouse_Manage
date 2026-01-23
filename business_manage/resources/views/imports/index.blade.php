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

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('imports.index') }}" method="GET">
            <div class="row">
                <!-- Tìm mã phiếu -->
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Mã phiếu (#PN...)" value="{{ request('search') }}">
                </div>

                <!-- Lọc Nhà cung cấp -->
                <div class="col-md-3">
                    <select name="supplier_id" class="form-control form-control-sm select2">
                        <option value="">-- Tất cả Nhà cung cấp --</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('imports.index') }}" class="btn btn-default btn-sm" title="Xóa lọc">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
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
                        <a href="{{ route('imports.show', $order->id) }}" class="btn btn-xs btn-default" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        {{-- Bạn cũng có thể thêm nút in nhanh ở đây --}}
                        <button class="btn btn-xs btn-default" title="In phiếu" onclick="window.location.href=`{{ route('imports.show', $order->id) }}`"><i class="fas fa-print"></i></button>
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
        <div class="">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection