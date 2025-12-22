@extends('layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lịch sử Kiểm kho</h3>
        <a href="{{ route('audits.create') }}" class="btn btn-primary btn-sm float-right">Lập phiếu kiểm mới</a>
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
</div>
@endsection