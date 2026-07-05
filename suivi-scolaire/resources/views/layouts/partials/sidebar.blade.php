@php
    $user = auth()->user();
    $isGestionnaire = $user && $user->isGestionnaire();
@endphp

<aside class="sidebar no-print">
    <div class="sidebar-brand">
        <div class="logo">🏫</div>
        <div class="name">
            {{ config('app.nom_ecole') }}
            <small>Suivi Scolaire</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Général</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Tableau de bord
        </a>

        @if ($isGestionnaire)
            <div class="sidebar-section">Administration</div>
            <a href="{{ route('eleves.index') }}" class="{{ request()->routeIs('eleves.*') ? 'active' : '' }}">
                <span class="icon">👨‍🎓</span> Élèves
            </a>
            <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
                <span class="icon">🏷️</span> Classes
            </a>
            <a href="{{ route('matieres.index') }}" class="{{ request()->routeIs('matieres.*') ? 'active' : '' }}">
                <span class="icon">📚</span> Matières
            </a>
            <a href="{{ route('enseignants.index') }}" class="{{ request()->routeIs('enseignants.*') ? 'active' : '' }}">
                <span class="icon">🧑‍🏫</span> Enseignants
            </a>
            <a href="{{ route('parents.index') }}" class="{{ request()->routeIs('parents.*') ? 'active' : '' }}">
                <span class="icon">👨‍👩‍👧</span> Comptes parents
            </a>

            <div class="sidebar-section">Établissement</div>
            <a href="{{ route('passage-annee.index') }}" class="{{ request()->routeIs('passage-annee.*') ? 'active' : '' }}">
                <span class="icon">🎓</span> Passage d'année
            </a>
            <a href="{{ route('parametres.index') }}" class="{{ request()->routeIs('parametres.*') ? 'active' : '' }}">
                <span class="icon">⚙️</span> Paramètres
            </a>

            <div class="sidebar-section">Finances</div>
            <a href="{{ route('paiements.index') }}" class="{{ request()->routeIs('paiements.*') ? 'active' : '' }}">
                <span class="icon">💳</span> Paiements
            </a>
            <a href="{{ route('impayes.index') }}" class="{{ request()->routeIs('impayes.*') ? 'active' : '' }}">
                <span class="icon">⚠️</span> Impayés
            </a>
        @endif

        <div class="sidebar-section">Pédagogie</div>
        <a href="{{ route('notes.index') }}" class="{{ request()->routeIs('notes.index') || request()->routeIs('notes.store') ? 'active' : '' }}">
            <span class="icon">✏️</span> Notes
        </a>
        <a href="{{ route('notes.classement') }}" class="{{ request()->routeIs('notes.classement') ? 'active' : '' }}">
            <span class="icon">🏆</span> Classement
        </a>
        <a href="{{ route('devoirs.index') }}" class="{{ request()->routeIs('devoirs.*') ? 'active' : '' }}">
            <span class="icon">📓</span> Cahier de notes
        </a>
        <a href="{{ route('absences.index') }}" class="{{ request()->routeIs('absences.*') ? 'active' : '' }}">
            <span class="icon">🚫</span> Absences
        </a>

        <div class="sidebar-section">Communication</div>
        <a href="{{ route('annonces.index') }}" class="{{ request()->routeIs('annonces.*') ? 'active' : '' }}">
            <span class="icon">📢</span> Annonces
        </a>
    </nav>

    <div class="sidebar-foot">
        Connecté en tant que<br>
        <strong style="color:#fff">{{ $isGestionnaire ? 'Gestionnaire' : 'Enseignant' }}</strong>
    </div>
</aside>
