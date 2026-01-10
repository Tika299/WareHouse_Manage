@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-outline card-danger shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-calculator"></i> Cấu hình công thức tính giá bán tự động</h3>
            </div>
            <form action="{{ route('pricing.updateAll') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- CÔNG THỨC THÔNG THƯỜNG -->
                        <div class="col-md-6 border-right">
                            <h5 class="text-primary border-bottom pb-2">Giá Bán Nội Bộ</h5>
                            <div class="form-group">
                                <label>Hệ số Giá Lẻ (Mặc định x1.5)</label>
                                <input type="number" step="0.01" name="factor_retail" class="form-control" value="1.50">
                                <small class="text-muted">Công thức: Giá vốn x 1.5</small>
                            </div>
                            <div class="form-group">
                                <label>Hệ số Giá Sỉ (Mặc định x1.1)</label>
                                <input type="number" step="0.01" name="factor_wholesale" class="form-control" value="1.10">
                                <small class="text-muted">Công thức: Giá vốn x 1.1</small>
                            </div>
                            <div class="form-group">
                                <label>Hệ số Giá CTV (Mặc định x1.2)</label>
                                <input type="number" step="0.01" name="factor_ctv" class="form-control" value="1.20">
                                <small class="text-muted">Công thức: Giá vốn x 1.2</small>
                            </div>
                        </div>

                        <!-- CÔNG THỨC SÀN TMĐT -->
                        <div class="col-md-6">
                            <h5 class="text-orange border-bottom pb-2">Giá Sàn TMĐT (Shopee, Lazada...)</h5>
                            <p class="text-sm">Công thức: <code>Vốn x (1 + Margin) / (1 - Phí sàn)</code></p>

                            <div class="form-group">
                                <label>Tỉ lệ Lợi nhuận mong muốn (Margin %)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="margin_eco" class="form-control" value="0.50">
                                    <div class="input-group-append"><span class="input-group-text">~ 50%</span></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Phí sàn chi trả (Phí cố định + Thuế %)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="fee_eco" class="form-control" value="0.30">
                                    <div class="input-group-append"><span class="input-group-text">~ 30%</span></div>
                                </div>
                            </div>

                            <div class="callout callout-info mt-4">
                                <h6>Ví dụ tính toán:</h6>
                                <small>Vốn 100k -> Giá sàn: 100k x (1 + 0.5) / (1 - 0.3) = <b>214,285 đ</b></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-danger btn-lg px-5 font-weight-bold">
                        CẬP NHẬT CÔNG THỨC TOÀN HỆ THỐNG
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection