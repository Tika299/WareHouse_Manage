@extends('layouts.app')
@section('title', 'Báo cáo công nợ tổng hợp')

@section('content')
<div class="container-fluid">
    <!-- TỔNG KẾT NHANH -->
    <div class="row">
        <div class="col-md-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TỔNG PHẢI THU (KHÁCH NỢ)</span>
                    <span class="info-box-number text-xl text-success">{{ number_format($totalReceivable) }} đ</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TỔNG PHẢI TRẢ (NỢ NCC)</span>
                    <span class="info-box-number text-xl text-danger">{{ number_format($totalPayable) }} đ</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- BẢNG KHÁCH HÀNG NỢ -->
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-tag"></i> Danh sách khách hàng nợ</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr class="text-13">
                                <th>Tên khách hàng</th>
                                <th>SĐT</th>
                                <th class="text-right">Số nợ</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="text-13">
                            @forelse($customerDebts as $c)
                            <tr>
                                <td class="font-weight-bold">{{ $c->name }}</td>
                                <td>{{ $c->phone }}</td>
                                <td class="text-right text-danger font-weight-bold">{{ number_format($c->total_debt) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('credit_logs.index') }}?target_type=customer&target_id={{ $c->id }}" class="btn btn-xs btn-info" title="Xem chi tiết đơn nợ">
                                        <i class="fas fa-search"></i>
                                    </a>
                                    <a href="{{ route('vouchers.create') }}?customer_id={{ $c->id }}" class="btn btn-xs btn-success" title="Thu tiền nợ">
                                        <i class="fas fa-check"></i> Thu nợ
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center p-3">Không có khách hàng nào nợ.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BẢNG NỢ NHÀ CUNG CẤP -->
        <div class="col-md-6">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-truck-loading"></i> Danh sách nợ Nhà cung cấp</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr class="text-13">
                                <th>Tên Nhà cung cấp</th>
                                <th>SĐT</th>
                                <th class="text-right">Mình nợ</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="text-13">
                            @forelse($supplierDebts as $s)
                            <tr>
                                <td class="font-weight-bold">{{ $s->name }}</td>
                                <td>{{ $s->phone }}</td>
                                <td class="text-right text-danger font-weight-bold">{{ number_format($s->total_debt) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('credit_logs.index') }}?target_type=supplier&target_id={{ $s->id }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-search"></i>
                                    </a>
                                    <a href="{{ route('vouchers.create') }}?supplier_id={{ $s->id }}" class="btn btn-xs btn-danger">
                                        <i class="fas fa-minus-circle"></i> Trả nợ
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center p-3">Không nợ nhà cung cấp nào.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection