@php
/*
Xử lý Logic hiển thị ở đầu file để tránh lỗi ParseError
*/
$activeGroup = $activeGroup ?? 'dashboard';
$activeName = $activeName ?? '';

// Kiểm tra quyền truy cập nhanh
$isAdmin = auth()->user()->hasRole('Admin');
$isKeToan = auth()->user()->hasAnyRole(['Admin', 'Kế toán']);
$isKho = auth()->user()->hasAnyRole(['Admin', 'Quản lý kho']);
@endphp

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống Quản lý Doanh nghiệp')</title>

    <!-- CSS Core -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dist/img/icon/favicontpt.ico') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <!-- Library CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('dist/css/custom.css') }}"> {{-- Chứa các class bổ sung --}}

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .navbar-head.active-navbar {
            border-bottom: 3px solid #fff;
            font-weight: bold;
        }

        .sub-nav-btn.active {
            background-color: #007bff !important;
            color: #fff !important;
            border-color: #007bff !important;
        }

        .bg-erp-main {
            background-color: #2c3e50 !important;
        }

        .height-47 {
            height: 47px;
        }

        .text-13 {
            font-size: 13px;
        }
    </style>
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <!-- NAVBAR CHÍNH (TOP) -->
        <nav class="main-header navbar navbar-expand-md navbar-dark bg-erp-main border-bottom-0 height-47">
            <div class="container-fluid">
                <!-- Logo -->
                <a href="/" class="navbar-brand">
                    <span class="brand-text font-weight-light"><b>ERP</b> QUẢN TRỊ</span>
                </a>

                <!-- Menu Chính -->
                <div class="collapse navbar-collapse justify-content-center" id="navbarCollapse">
                    <ul class="navbar-nav">
                        {{-- TỔNG QUAN --}}
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link navbar-head {{ $activeGroup == 'dashboard' ? 'active-navbar' : '' }}">DASHBOARD</a>
                        </li>

                        {{-- QUẢN LÝ KHO --}}
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle navbar-head {{ $activeGroup == 'inventory' ? 'active-navbar' : '' }}">KHO HÀNG</a>
                            <ul class="dropdown-menu border-0 shadow">
                                <li><a href="{{ route('products.index') }}" class="dropdown-item">Hàng hóa (Sản phẩm)</a></li>
                                <li><a href="{{ route('imports.index') }}" class="dropdown-item">Nhập kho (Giá vốn)</a></li>
                                <!-- <li><a href="{{ route('inventoryLookup.index') }}" class="dropdown-item">Tra cứu tồn kho</a></li> -->
                                <li><a href="{{ route('audits.index') }}" class="dropdown-item">Kiểm kê kho</a></li>
                                <li><a href="{{ route('internal_exports.index') }}" class="dropdown-item">Xuất kho nội bộ</a></li>
                                <li><a href="{{ route('pricing.index') }}" class="dropdown-item">Chính sách giá</a></li>
                                @if($isAdmin)
                                <li><a href="{{ route('warehouses.index') }}" class="dropdown-item">Cấu hình kho</a></li>
                                @endif
                            </ul>
                        </li>

                        {{-- BÁN HÀNG --}}
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle navbar-head {{ $activeGroup == 'sales' ? 'active-navbar' : '' }}">BÁN HÀNG</a>
                            <ul class="dropdown-menu border-0 shadow">
                                <li><a href="{{ route('exports.index') }}" class="dropdown-item">Tạo đơn hàng / Xuất kho</a></li>
                                <li><a href="{{ route('customers.index') }}" class="dropdown-item">Khách hàng & Công nợ</a></li>
                                <li><a href="{{ route('returnforms.index') }}" class="dropdown-item">Đổi trả hàng (Barter)</a></li>
                                <li><a href="{{ route('shipping_units.index') }}" class="dropdown-item">Đơn vị vận chuyển</a></li>
                            </ul>
                        </li>

                        {{-- TÀI CHÍNH --}}
                        @if($isKeToan)
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle navbar-head {{ $activeGroup == 'finance' ? 'active-navbar' : '' }}">TÀI CHÍNH</a>
                            <ul class="dropdown-menu border-0 shadow">
                                <li><a href="{{ route('vouchers.index') }}" class="dropdown-item">Phiếu Thu / Phiếu Chi</a></li>
                                <li><a href="{{ route('accounts.index') }}" class="dropdown-item">Sổ quỹ (Tiền mặt/Ngân hàng)</a></li>
                                <li><a href="{{ route('providers.index') }}" class="dropdown-item">Nhà cung cấp</a></li>
                                <li><a href="{{ route('credit_logs.index') }}" class="dropdown-item">Lịch sử nợ</a></li>
                            </ul>
                        </li>
                        @endif

                        {{-- BÁO CÁO --}}
                        @if($isKeToan)
                        <li class="nav-item dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle navbar-head {{ $activeGroup == 'reports' ? 'active-navbar' : '' }}">BÁO CÁO</a>
                            <ul class="dropdown-menu border-0 shadow">
                                <li><a href="{{ route('reportOverview') }}" class="dropdown-item">Lãi lỗ (P&L)</a></li>
                                <li><a href="{{ route('reportPayments') }}" class="dropdown-item">Doanh thu & Thanh toán</a></li>
                                <li><a href="{{ route('reportDebt') }}" class="dropdown-item">Công nợ tổng hợp</a></li>
                                <li><a href="{{ route('reportExportImport') }}" class="dropdown-item">Kho & Kiểm hàng</a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- User Info & Logout -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-user-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-header">{{ Auth::user()->email }}</span>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('profile.users.edit') }}" class="dropdown-item">Thông tin cá nhân</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Đăng xuất</button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- THANH NAVIGATION PHỤ (SUB-NAV) -->
        @if(in_array($activeGroup, ['inventory', 'sales', 'finance', 'reports']))
        <div class="bg-light border-bottom px-3 py-2">
            <div class="d-flex flex-wrap">
                {{-- ... Code Kho, Bán hàng, Tài chính ... --}}

                @if($activeGroup == 'inventory')
                <a href="{{ route('products.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'products' ? 'active' : '' }}">Sản phẩm</button></a>
                <a href="{{ route('imports.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'imports' ? 'active' : '' }}">Nhập kho</button></a>
                <!-- <a href="{{ route('inventoryLookup.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'stock' ? 'active' : '' }}">Tồn kho</button></a> -->
                <a href="{{ route('audits.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'audits' ? 'active' : '' }}">Kiểm hàng</button></a>
                <a href="{{ route('internal_exports.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'internal_exports' ? 'active' : '' }}">Xuất nội bộ</button></a>
                <a href="{{ route('pricing.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'pricing' ? 'active' : '' }}">Chính sách Giá</button></a>
                @endif

                @if($activeGroup == 'sales')
                <a href="{{ route('exports.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'orders' ? 'active' : '' }}">Đơn hàng</button></a>
                <a href="{{ route('customers.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'customers' ? 'active' : '' }}">Khách hàng</button></a>
                <a href="{{ route('shipping_units.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'shipping' ? 'active' : '' }}">Vận chuyển</button></a>
                @endif

                @if($activeGroup == 'finance')
                <a href="{{ route('vouchers.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'vouchers' ? 'active' : '' }}">Thu / Chi</button></a>
                <a href="{{ route('accounts.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'accounts' ? 'active' : '' }}">Sổ quỹ</button></a>
                <a href="{{ route('providers.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'providers' ? 'active' : '' }}">Nhà cung cấp</button></a>
                <a href="{{ route('credit_logs.index') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'credit_logs' ? 'active' : '' }}">Lịch sử nợ</button></a>
                @endif

                @if($activeGroup == 'reports')
                <a href="{{ route('reportOverview') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'overview' ? 'active' : '' }}">Lãi lỗ (P&L)</button></a>
                <a href="{{ route('reportPayments') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'payments' ? 'active' : '' }}">Doanh thu & Thanh toán</button></a>
                <a href="{{ route('reportDebt') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'debt' ? 'active' : '' }}">Công nợ tổng hợp</button></a>
                <a href="{{ route('reportExportImport') }}"><button class="btn btn-sm btn-outline-secondary mr-2 sub-nav-btn {{ $activeName == 'export_import' ? 'active' : '' }}">Kho & Kiểm hàng</button></a>
                @endif
            </div>
        </div>
        @endif

        <!-- THÔNG BÁO FLASH (MSG) -->
        @if (session('msg') || session('warning'))
        <div id="alert-box" style="position: fixed; top: 60px; right: 20px; z-index: 9999; min-width: 300px;">
            @if (session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                {!! session('msg') !!} {{-- Sửa dấu {{ }} thành {!! !!} ở đây (Dòng 140) --}}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            @endif

            @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show">
                {!! session('warning') !!} {{-- Sửa dấu {{ }} thành {!! !!} ở đây (Dòng 146) --}}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            @endif
        </div>
        @endif
</body>

<script>
    $(document).ready(function() {
        // Tự động ẩn thông báo
        setTimeout(function() {
            $('.alert-dismissible').fadeOut('slow');
        }, 3000);
    });
</script>

</html>