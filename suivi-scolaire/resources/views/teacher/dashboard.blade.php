@extends('layouts.app')

@section('title', 'Espace Enseignant')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', 'Espace Enseignant')

@php
    $annee = $annee ?? config('app.annee_scolaire');
@endphp

@section('content')

    <div class="welcome welcome-accent">
        <h2>👋 Bonjour, {{ auth()->user()->name }} !</h2>
        @if($classe)
            <p>Vous enseignez en classe : <strong>{{ $classe->nom }}</strong> — Année {{ $annee }}</p>
        @else
            <p>Aucune classe ne vous est assignée pour le moment.</p>
        @endif
    </div>

    @if($classe)
        <form method="GET" action="{{ route('dashboard') }}" style="margin-bottom:14px">
            <div class="form-group" style="margin-bottom:0; max-width:200px">
                <label>Trimestre affiché</label>
                <select name="trimestre" onchange="this.form.submit()">
                    <option value="1" {{ $trimestre == 1 ? 'selected' : '' }}>Trimestre 1</option>
                    <option value="2" {{ $trimestre == 2 ? 'selected' : '' }}>Trimestre 2</option>
                    <option value="3" {{ $trimestre == 3 ? 'selected' : '' }}>Trimestre 3</option>
                </select>
            </div>
        </form>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-value">{{ $nbEleves }}</div>
                <div class="stat-label">Élèves dans ma classe</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value">{{ $nbMatieres }}</div>
                <div class="stat-label">Matières configurées</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✏️</div>
                <div class="stat-value">{{ $nbNotes }}</div>
                <div class="stat-label">Notes saisies par moi</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value" style="color:{{ $moyenneClasse !== null && $moyenneClasse < 5 ? '#b91c1c' : '#059669' }}">
                    {{ $moyenneClasse !== null ? $moyenneClasse . '/10' : '—' }}
                </div>
                <div class="stat-label">Moyenne générale — Trim. {{ $trimestre }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">⚡ Actions rapides</div>
            <div class="card-body">
                <a href="{{ route('notes.index', ['classe_id' => $classe->id]) }}" class="btn-action btn-green">
                    ✏️ Saisir les notes — {{ $classe->nom }}
                </a>
                <a href="{{ route('notes.classement', ['classe_id' => $classe->id]) }}" class="btn-action btn-blue">
                    🏆 Voir le classement — {{ $classe->nom }}
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                👨‍🎓 Mes élèves — {{ $classe->nom }}
                <span class="muted">{{ $nbEleves }} élève(s)</span>
            </div>
            <div class="card-body no-pad">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom &amp; Prénom</th>
                                <th>Sexe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($eleves as $eleve)
                                <tr>
                                    <td style="font-family:monospace; color:var(--color-text-muted)">{{ $eleve->matricule }}</td>
                                    <td style="font-weight:600">{{ $eleve->prenom }} {{ $eleve->nom }}</td>
                                    <td>{{ $eleve->sexe === 'M' ? '♂ Garçon' : '♀ Fille' }}</td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="3">Aucun élève dans cette classe.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <div class="icon">⚠️</div>
                Aucune classe ne vous est assignée.<br>Contactez le gestionnaire.
            </div>
        </div>
    @endif

@endsection
