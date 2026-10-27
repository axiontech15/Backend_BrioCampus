<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Brio Campus')</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.min.css') }}">
    @stack('styles')
</head>

<body>
    @if (session('success'))
        <div class="toast" role="status" style="position:fixed;right:16px;bottom:16px;z-index:50">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="toast" role="alert" style="position:fixed;right:16px;bottom:16px;z-index:50;background:#C2362B;color:#fff">{{ session('error') }}</div>
    @endif

    @yield('content')

    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
