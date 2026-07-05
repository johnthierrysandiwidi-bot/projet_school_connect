@extends('layouts.app')

@section('title', 'Impayés')
@section('page-title', 'Élèves en situation d\'impayé')
@section('page-subtitle', $annee)

@section('content')

    <div class="stats-grid" style="margin-bottom:18px">
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value">{{ $impayes->count() }}</div>
            <div class="stat-label">Élève(s) en impayé</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value" style="color:var(--color-danger)">{{ number_format($montantTotalDu, 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Montant total dû</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('impayes.index') }}">
                <div class="filtre-group">
                    <div class="form-group" style="margin-bottom:0">
                        <label>Rechercher</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, prénom ou matricule">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label>Classe</label>
                        <select name="classe_id">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ request('classe_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                    @if(request('search') || request('classe_id'))
                    <a href="{{ route('impayes.index') }}" class="btn btn-outline">✕ Effacer</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">⚠️ Liste des impayés ({{ $impayes->count() }})</div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Matricule</th>
                        <th>Classe</th>
                        <th>Frais total</th>
                        <th>Payé</th>
                        <th>Reste à payer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($impayes as $eleve)
                    @php
                        $frais = $eleve->classe->frais_scolarite ?? 0;
                        $pct = $frais > 0 ? round($eleve->montant_paye / $frais * 100) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong></td>
                        <td style="font-family:monospace; color:var(--color-text-muted); font-size:12px">{{ $eleve->matricule }}</td>
                        <td><span class="badge badge-blue">{{ $eleve->classe->nom ?? '-' }}</span></td>
                        <td>{{ number_format($frais, 0, ',', ' ') }} FCFA</td>
                        <td>
                            {{ number_format($eleve->montant_paye, 0, ',', ' ') }} FCFA
                            <div class="form-hint">{{ $pct }}% réglé</div>
                        </td>
                        <td style="color:var(--color-danger); font-weight:bold">
                            {{ number_format($eleve->reste_a_payer, 0, ',', ' ') }} FCFA
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('paiements.create', ['eleve_id' => $eleve->id]) }}" class="btn btn-primary btn-sm">💰 Encaisser</a>
                                <a href="{{ route('eleves.show', $eleve) }}" class="btn btn-outline btn-sm">👁 Voir</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7">✅ Aucun impayé{{ request('search') || request('classe_id') ? ' pour ce filtre' : '' }} !</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
