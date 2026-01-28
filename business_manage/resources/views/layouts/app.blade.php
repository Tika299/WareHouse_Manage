<!-- resources/views/layouts/app.blade.php -->
@include('partials.header')

<div class="content-wrapper" style="min-height: 100vh; background-color: #f4f6f9; padding-top: 20px;">
    <section class="content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </section>
</div>

<!-- Footer/Scripts chung -->
@push('scripts')
<!-- Đảm bảo đã nạp Select2 CSS/JS trong layout, nếu chưa có thì uncomment dòng dưới -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@stack('scripts')