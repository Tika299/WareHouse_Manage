@extends('layouts.app')
@section('title', 'Danh sách phiếu hoàn trả NCC')

@section('content')
<div class="card card-outline card-danger shadow">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title font-weight-bold mb-0">Danh sách phiếu hoàn trả NCC</h3>
        <a href="{{ route('purchase-returns.create') }}" class="btn btn-danger btn-sm font-weight-bold">
            <i class="fas fa-plus"></i> Tạo phiếu hoàn trả
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="bg-light text-13">
                <tr>
                    <th>Mã phiếu</th>
                    <th>Phiếu nhập gốc</th>
                    <th>Nhà cung cấp</th>
                    <th>Ngày hoàn trả</th>
                    <th class="text-right">Giá trị hoàn trả</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $item)
                    <tr>
                        <td>
                            <a href="{{ route('purchase-returns.show', $item->id) }}" class="font-weight-bold">
                                {{ $item->return_code }}
                            </a>
                        </td>
                        <td>#PN{{ str_pad($item->purchase_order_id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $item->supplier->name ?? '-' }}</td>
                        <td>{{ optional($item->returned_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-right">{{ number_format($item->total_return_value) }} đ</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $item->status === 'completed' ? 'success' : 'secondary' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('purchase-returns.show', $item->id) }}" class="btn btn-xs btn-default" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4">Chưa có phiếu hoàn trả nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $returns->links() }}
    </div>
</div>
@endsection
