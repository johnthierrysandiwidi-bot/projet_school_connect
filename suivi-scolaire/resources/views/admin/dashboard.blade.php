@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-subtitle', "Vue d'ensemble de l'établissement")

@section('content')

    {{-- Sélecteur de trimestre pour l'aperçu pédagogique --}}
    <div class="page-header">
        <div></div>
        <form method="GET" action="{{ route('dashboard') }}" class="page-actions">
            <div class="form-group" style="margin-bottom:0">
                <select name="trimestre" onchange="this.form.submit()">
                    <option value="1" {{ $trimestre == 1 ? 'selected' : '' }}>Trimestre 1</option>
                    <option value="2" {{ $trimestre == 2 ? 'selected' : '' }}>Trimestre 2</option>
                    <option value="3" {{ $trimestre == 3 ? 'selected' : '' }}>Trimestre 3</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card" style="display:flex; align-items:center; gap:16px; text-align:left;">
            <div class="stat-icon" style="background:#dbeafe; width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0;">👨‍🎓</div>
            <div>
                <div class="stat-value">{{ $totalEleves }}</div>
                <div class="stat-label">Élèves inscrits</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:16px; text-align:left;">
            <div class="stat-icon" style="background:#d1fae5; width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0;">🏫</div>
            <div>
                <div class="stat-value">{{ $totalClasses }}</div>
                <div class="stat-label">Classes</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:16px; text-align:left;">
            <div class="stat-icon" style="background:#fef3c7; width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0;">💰</div>
            <div>
                <div class="stat-value">{{ number_format($fraisCollectes, 0, ',', ' ') }}</div>
                <div class="stat-label">FCFA collectés</div>
            </div>
        </div>
        <div class="stat-card" style="display:flex; align-items:center; gap:16px; text-align:left;">
            <div class="stat-icon" style="background:#fee2e2; width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin:0;">⚠️</div>
            <div>
                <div class="stat-value">{{ number_format($resteTotal, 0, ',', ' ') }}</div>
                <div class="stat-label">FCFA restants</div>
            </div>
        </div>
    </div>

    {{-- Taux de recouvrement --}}
    <div class="card">
        <div class="card-body">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <div>
                    <strong>Taux de recouvrement</strong>
                    <div class="form-hint" style="margin-top:2px">
                        {{ number_format($fraisCollectes, 0, ',', ' ') }} FCFA collectés
                        sur {{ number_format($fraisAttendus, 0, ',', ' ') }} FCFA attendus
                    </div>
                </div>
                <div style="font-size:28px; font-weight:bold;
                    color:{{ $taux >= 70 ? '#059669' : ($taux >= 40 ? '#d97706' : '#b91c1c') }}">
                    {{ $taux }}%
                </div>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill"
                     style="width:{{ $taux }}%;
                     background:{{ $taux >= 70 ? '#059669' : ($taux >= 40 ? '#d97706' : '#ef4444') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="form-grid">

        {{-- Stats par classe --}}
        <div class="card" style="grid-column:auto;">
            <div class="card-header">📊 Récapitulatif par classe</div>
            <div class="card-body no-pad">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Élèves</th>
                                <th>Attendus</th>
                                <th>Collectés</th>
                                <th>Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statsClasses as $classe)
                            @php
                                $t = $classe->frais_attendus > 0
                                    ? round($classe->frais_collectes / $classe->frais_attendus * 100, 1)
                                    : 0;
                            @endphp
                            <tr>
                                <td><span class="badge badge-blue">{{ $classe->nom }}</span></td>
                                <td>{{ $classe->nb_eleves }}</td>
                                <td>{{ number_format($classe->frais_attendus, 0, ',', ' ') }}</td>
                                <td style="color:#059669; font-weight:bold">
                                    {{ number_format($classe->frais_collectes, 0, ',', ' ') }}
                                </td>
                                <td style="color:{{ $t >= 70 ? '#059669' : ($t >= 40 ? '#d97706' : '#b91c1c') }}; font-weight:bold">
                                    {{ $t }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Élèves impayés --}}
        <div class="card" style="grid-column:auto;">
            <div class="card-header">
                ⚠️ Élèves avec impayés
                <a href="{{ route('impayes.index') }}" style="font-size:12px">Voir tout →</a>
            </div>
            <div class="card-body no-pad">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Classe</th>
                                <th>Reste</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($elevesImpayes as $eleve)
                            <tr>
                                <td>{{ $eleve->nom }} {{ $eleve->prenom }}</td>
                                <td><span class="badge badge-blue">{{ $eleve->classe->nom ?? '-' }}</span></td>
                                <td style="color:#b91c1c; font-weight:bold">
                                    {{ number_format($eleve->reste_a_payer, 0, ',', ' ') }}
                                </td>
                                <td>
                                    <a href="{{ route('paiements.create', ['eleve_id' => $eleve->id]) }}"
                                       class="btn btn-primary btn-sm">Payer</a>
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row"><td colspan="4">✅ Aucun impayé !</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Aperçu pédagogique --}}
    <div class="card">
        <div class="card-header">
            📚 Aperçu pédagogique — Trimestre {{ $trimestre }} — {{ $annee }}
            <a href="{{ route('notes.classement') }}" style="font-size:12px">Voir le classement →</a>
        </div>
        <div class="card-body no-pad">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Matières</th>
                            <th>Moyenne générale</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statsPedagogiques as $classe)
                        <tr>
                            <td><span class="badge badge-blue">{{ $classe->nom }}</span></td>
                            <td>{{ $classe->nb_matieres }}</td>
                            <td>
                                @if($classe->moyenne_generale !== null)
                                    <strong style="color:{{ $classe->moyenne_generale >= 10 ? '#059669' : '#b91c1c' }}">
                                        {{ $classe->moyenne_generale }}/10
                                    </strong>
                                @else
                                    <span style="color:#94a3b8">Pas encore de notes</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('notes.classement', ['classe_id' => $classe->id, 'trimestre' => $trimestre]) }}"
                                   class="btn btn-outline btn-sm">Détails</a>
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row"><td colspan="4">Aucune classe configurée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Paiements récents --}}
    <div class="card">
        <div class="card-header">
            🕐 Paiements récents
            <a href="{{ route('paiements.index') }}" style="font-size:12px">Voir tout →</a>
        </div>
        <div class="card-body no-pad">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paiementsRecents as $p)
                        <tr>
                            <td style="font-family:monospace; color:var(--color-text-muted)">{{ $p->reference }}</td>
                            <td>{{ $p->eleve->prenom }} {{ $p->eleve->nom }}</td>
                            <td><span class="badge badge-blue">{{ $p->eleve->classe->nom ?? '-' }}</span></td>
                            <td style="color:#059669; font-weight:bold">
                                {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr class="empty-row"><td colspan="5">Aucun paiement enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
