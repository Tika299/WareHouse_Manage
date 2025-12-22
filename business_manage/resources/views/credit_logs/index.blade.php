@extends('layouts.app')
@section('title', 'Nhật ký biến động nợ')

@section('content')
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">Đối soát công nợ tổng hợp</h3>
    </div>
    <div class="card-body">
        <!-- BỘ LỌC TÌM KIẾM -->
        <form action="{{ route('credit_logs.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <label>Đối tượng</label>
                    <select name="target_type" class="form-control form-control-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="customer" {{ request('target_type') == 'customer' ? 'selected' : '' }}>Khách hàng</option>
                        <option value="supplier" {{ request('target_type') == 'supplier' ? 'selected' : '' }}>Nhà cung cấp</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Từ ngày</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Đến ngày</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter"></i> Lọc dữ liệu</button>
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <a href="{{ route('credit_logs.index') }}" class="btn btn-default btn-sm btn-block">Xóa lọc</a>
                </div>
            </div>
        </form>

        <!-- BẢNG DỮ LIỆU -->
        <table class="table table-sm table-bordered table-striped table-hover">
            <thead>
                <tr class="bg-light text-13">
                    <th>Ngày/Giờ</th>
                    <th>Đối tượng</th>
                    <th>Loại</th>
                    <th>Mã tham chiếu</th>
                    <th>Nội dung / Lý do</th>
                    <th class="text-right">Biến động</th>
                    <th class="text-right">Dư nợ cuối</th>
                </tr>
            </thead>
            <tbody class="text-12">
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="font-weight-bold">
                        @if($log->target_type == 'customer')
                            <span class="text-primary"><i class="fas fa-user"></i> {{ $log->target->name ?? 'N/A' }}</span>
                        @else
                            <span class="text-orange"><i class="fas fa-truck"></i> {{ $log->target->name ?? 'N/A' }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $log->change_amount > 0 ? 'badge-danger' : 'badge-success' }}">
                            {{ $log->change_amount > 0 ? 'TĂNG NỢ' : 'GIẢM NỢ' }}
                        </span>
                    </td>
                    <td>
                        <small class="text-uppercase font-weight-bold">{{ $log->ref_type }} #{{ $log->ref_id }}</small>
                    </td>
                    <td>{{ $log->note }}</td>
                    <td class="text-right font-weight-bold {{ $log->change_amount > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $log->change_amount > 0 ? '+' : '' }}{{ number_format($log->change_amount) }}
                    </td>
                    <td class="text-right font-weight-bold text-dark">
                        {{ number_format($log->new_balance) }} đ
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center p-4 text-muted">Không tìm thấy nhật ký nợ nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $logs->links() }}
    </div>
</div>
@endsection