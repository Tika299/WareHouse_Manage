@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lịch sử Kiểm kho</h3>
        <a href="{{ route('audits.create') }}" class="btn btn-primary btn-sm float-right">Lập phiếu kiểm mới</a>
    </div>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card-body border-bottom bg-light">
        <form action="{{ route('audits.index') }}" method="GET">
            <div class="row">
                <!-- Tìm mã phiếu -->
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Mã phiếu (#AUD...)" value="{{ request('search') }}">
                </div>

                <!-- Lọc Ngày -->
                <div class="col-md-3 d-flex">
                    <input type="date" name="form_date" class="form-control form-control-sm mr-1" value="{{ request('form_date') }}">
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>

                <!-- Nút bấm -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-sm">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('audits.index') }}" class="btn btn-default btn-sm" title="Xóa lọc">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Mã phiếu</th>
                    <th>Ngày kiểm</th>
                    <th>Người thực hiện</th>
                    <th>Ghi chú</th>
                    <th class="text-right">Tổng lệch (Tiền)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $a)
                <tr>
                    <td>#AUD{{ $a->id }}</td>
                    <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $a->user->name }}</td>
                    <td>{{ $a->note }}</td>
                    <td class="text-right {{ $a->total_diff_value < 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($a->total_diff_value) }} đ
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{-- Phân trang --}}
        <div class="">
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection