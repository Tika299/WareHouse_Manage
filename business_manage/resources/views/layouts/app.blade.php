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
@stack('scripts')