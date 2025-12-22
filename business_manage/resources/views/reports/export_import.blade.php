@extends('layouts.app')
@section('title', 'Báo cáo Xuất Nhập Tồn')
@section('content')
<div class="card">
    <div class="card-header bg-dark"><h3 class="card-title">Theo dõi biến động kho hàng</h3></div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center">Tồn đầu kỳ</th>
                    <th class="text-center text-success">Tổng nhập</th>
                    <th class="text-center text-danger">Tổng xuất</th>
                    <th class="text-center text-primary">Tồn cuối kỳ</th>
                    <th class="text-right">Giá trị tồn kho</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td class="text-center">--</td> {{-- Logic tính tồn đầu kỳ cần query StockLog --}}
                    <td class="text-center">{{ $p->stockLogs()->where('ref_type', 'import')->sum('change_qty') }}</td>
                    <td class="text-center">{{ abs($p->stockLogs()->where('ref_type', 'export')->sum('change_qty')) }}</td>
                    <td class="text-center font-weight-bold">{{ $p->stock_quantity }}</td>
                    <td class="text-right font-weight-bold text-primary">{{ number_format($p->stock_quantity * $p->cost_price) }} đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection