@php
    $user = auth()->user();
@endphp

<header class="topbar no-print">
    <div style="display:flex; align-items:center; gap:14px;">
        <button class="menu-toggle" data-menu-toggle aria-label="Ouvrir le menu">☰</button>
        <div>
            <div class="page-title">@yield('page-title', 'Tableau de bord')</div>
            @hasSection('page-subtitle')
                <div class="page-subtitle">@yield('page-subtitle')</div>
            @endif
        </div>
    </div>

    <div class="topbar-user">
        <div class="who">
            <div class="name">{{ $user->name }}</div>
            <div class="role">{{ $user->isGestionnaire() ? 'Gestionnaire' : 'Enseignant' }}</div>
        </div>
        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <a href="{{ route('profile.password.edit') }}" class="btn btn-outline btn-sm" title="Changer mon mot de passe">🔑</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" title="Déconnexion">⏻</button>
        </form>
    </div>
</header>
