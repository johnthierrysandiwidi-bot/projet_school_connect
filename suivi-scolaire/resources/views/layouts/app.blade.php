<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tableau de bord') — {{ config('app.name', 'Suivi Scolaire') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>

<div class="app-shell">
    @include('layouts.partials.sidebar')

    <div class="main">
        @include('layouts.partials.topbar')

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success" data-autohide>✅ {{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" data-autohide>⚠️ {{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <div>
                        Veuillez corriger les erreurs suivantes :
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
