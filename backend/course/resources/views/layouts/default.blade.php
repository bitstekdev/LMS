<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Laravel App')</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/default/css/custom_style.css') }}">

    <!-- Page-specific CSS -->
    @stack('css')

    <!-- jQuery -->
    <script src="{{ asset('assets/frontend/default/js/jquery-3.7.1.min.js') }}"></script>
</head>

<body class="auth-body">
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/frontend/default/js/bootstrap.bundle.min.js') }}"></script>
    @stack('js')
</body>

</html>
