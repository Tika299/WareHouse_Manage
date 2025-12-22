@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>1.2 tỷ</h3><p>Tổng doanh thu</p></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>800 tr</h3><p>Giá vốn hàng bán (COGS)</p></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>400 tr</h3><p>Lợi nhuận gộp</p></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>320 tr</h3><p>Lợi nhuận Ròng (Lãi thực)</p></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Biểu đồ tăng trưởng (Mẫu)</h3></div>
    <div class="card-body text-center">
        <p class="text-muted">Biểu đồ doanh thu theo tháng sẽ hiển thị ở đây.</p>
    </div>
</div>
@endsection