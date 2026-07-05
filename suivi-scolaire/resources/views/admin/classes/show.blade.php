@extends('layouts.app')

@section('title', 'Classe ' . $classe->nom)
@section('page-title', 'Classe ' . $classe->nom)
@section('page-subtitle', "{$classe->niveau} — {$classe->annee_scolaire}")

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('classes.edit', $classe) }}" class="btn" style="background:#d97706; color:#fff;">✏️ Modifier</a>
            <a href="{{ route('classes.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div class="stat-value">{{ $classe->eleves->count() }}</div>
            <div class="stat-label">Élèves actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">{{ number_format($classe->frais_scolarite, 0, ',', ' ') }}</div>
            <div class="stat-label">FCFA — Frais de scolarité</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-value">{{ $classe->matieres->count() }}</div>
            <div class="stat-label">Matières</div>
        </div>
    </div>

    {{-- Matières --}}
    <div class="card">
        <div class="card-header">
            <span>📚 Matières</span>
            <a href="{{ route('classes.edit', $classe) }}" style="font-size:12px">✏️ Modifier →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Coefficient</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classe->matieres as $matiere)
                    <tr>
                        <td>{{ $matiere->nom }}</td>
                        <td><span class="badge badge-blue">{{ $matiere->coefficient }}</span></td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="2">Aucune matière configurée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Élèves --}}
    <div class="card">
        <div class="card-header">
            <span>👨‍🎓 Élèves inscrits</span>
            <a href="{{ route('eleves.create') }}" style="font-size:12px">+ Inscrire un élève →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Prénom &amp; Nom</th>
                        <th>Sexe</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classe->eleves as $eleve)
                    <tr>
                        <td style="font-family:monospace; color:var(--color-text-muted)">{{ $eleve->matricule }}</td>
                        <td>
                            <a href="{{ route('eleves.show', $eleve) }}">{{ $eleve->prenom }} {{ $eleve->nom }}</a>
                        </td>
                        <td>{{ $eleve->sexe === 'M' ? '♂ Garçon' : '♀ Fille' }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="3">Aucun élève inscrit dans cette classe.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
