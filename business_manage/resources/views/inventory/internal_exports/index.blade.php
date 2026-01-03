@extends('layouts.app')

@section('title', 'Danh sách xuất kho nội bộ')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Nhật ký Xuất kho nội bộ</h3>
        <div class="card-tools ml-auto">
            <a href="{{ route('internal_exports.create') }}" class="btn btn-danger btn-sm">
                <i class="fas fa-plus"></i> Tạo phiếu xuất nội bộ
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr class="bg-light text-13">
                    <th>Mã phiếu</th>
                    <th>Ngày xuất</th>
                    <th>Lý do</th>
                    <th>Người thực hiện</th>
                    <th class="text-right">Giá trị hàng (Vốn)</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-13">
                @forelse($exports as $export)
                <tr>
                    <td class="font-weight-bold">#IE{{ str_pad($export->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $export->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge badge-warning">{{ $export->reason_type }}</span>
                    </td>
                    <td>{{ $export->user->name }}</td>
                    <td class="text-right font-weight-bold text-danger">
                        {{ number_format($export->total_cost_value) }} đ
                    </td>
                    <td class="text-muted">{{ Str::limit($export->note, 30) }}</td>
                    <td class="text-center">
                        <a href="{{ route('internal_exports.show', $export->id) }}" class="btn btn-xs btn-default" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-xs btn-default" title="In phiếu"><i class="fas fa-print"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-muted">Chưa có phiếu xuất kho nội bộ nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">
            {{ $exports->links() }}
        </div>
    </div>
</div>
@endsection