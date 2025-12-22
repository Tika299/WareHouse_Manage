@extends('layouts.app')

@section('title', 'Quản lý Đổi trả hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Danh sách Phiếu Đổi trả / Barter</h3>
        <div class="card-tools ml-auto">
            <!-- Nút dẫn sang trang nghiệp vụ Đổi hàng đã làm ở bước trước -->
            <a href="{{ route('exports.barter') }}" class="btn btn-warning btn-sm font-weight-bold">
                <i class="fas fa-sync-alt"></i> Tạo phiếu Đổi hàng (Barter)
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light text-13">
                    <th>Mã phiếu</th>
                    <th>Ngày giao dịch</th>
                    <th>Khách hàng</th>
                    <th class="text-right">Giá trị xuất</th>
                    <th class="text-right">Giá trị thu về</th>
                    <th class="text-right">Chênh lệch</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @forelse($returns as $item)
                <tr>
                    <td class="font-weight-bold">#BT{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->customer->name ?? 'N/A' }}</td>
                    <td class="text-right text-danger">{{ number_format($item->total_product_amount) }} đ</td>
                    <td class="text-right text-success">
                        {{-- Giả sử chúng ta lấy giá trị thu về từ quan hệ nếu có, 
                             ở đây hiển thị minh họa theo logic chênh lệch nợ --}}
                        {{ number_format($item->total_product_amount - $item->paid_amount) }} đ
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ number_format($item->paid_amount) }} đ
                    </td>
                    <td class="text-center">
                        <a href="{{ route('exports.show', $item->id) }}" class="btn btn-xs btn-default">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-muted">Chưa có giao dịch đổi trả nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $returns->links() }}
    </div>
</div>
@endsection