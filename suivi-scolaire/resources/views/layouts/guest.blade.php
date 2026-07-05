<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion') — {{ config('app.name', 'Suivi Scolaire') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
    <div class="guest-wrap">
        <div class="guest-card">
            @yield('content')
        </div>
    </div>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
