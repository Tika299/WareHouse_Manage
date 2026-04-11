@extends('layouts.app')

@section('title', 'Danh sách Phiếu trả hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Quản lý Phiếu Khách trả hàng</h3>
        <div class="card-tools ml-auto">
            <a href="{{ route('customer_returns.create') }}" class="btn btn-warning btn-sm font-weight-bold">
                <i class="fas fa-plus"></i> Tạo phiếu trả hàng
            </a>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('customer_returns.index') }}" method="GET">
            <div class="row align-items-end">
                <!-- Lọc Khách hàng bằng Select2 Ajax -->
                <div class="col-md-3">
                    <x-select2-ajax
                        name="customer_id"
                        label=""
                        :url="route('customers.searchAjax')"
                        placeholder="-- Tìm khách hàng --"
                        :value="request('customer_id')"
                        :text="$selectedCustomer ? $selectedCustomer->name : null" />
                </div>

                <!-- Lọc Ngày -->
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        <div class="input-group-append">
                            <span class="input-group-text">đến</span>
                        </div>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Nút bấm -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="{{ route('customer_returns.index') }}" class="btn btn-outline-secondary btn-sm" title="Xóa lọc">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr class="bg-light text-13">
                    <th width="120">Mã phiếu</th>
                    <th>Ngày trả</th>
                    <th>Khách hàng</th>
                    <th>Người lập phiếu</th>
                    <th class="text-right">Giá trị hoàn trả</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @forelse($returns as $item)
                <tr>
                    <td class="font-weight-bold">#RE{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('customers.show', $item->customer_id) }}" class="font-weight-bold">
                            {{ $item->customer->name }}
                        </a>
                    </td>
                    <td>{{ $item->user->name }}</td>
                    <td class="text-right text-danger font-weight-bold">
                        - {{ number_format($item->total_return_value) }} đ
                    </td>
                    <td class="text-muted">{{ Str::limit($item->note, 40) }}</td>
                    <td class="text-center">
                        {{-- Nút xem chi tiết phiếu trả --}}
                        <button class="btn btn-xs btn-default" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-xs btn-default" title="In phiếu"><i class="fas fa-print"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-muted">Chưa có phiếu trả hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection