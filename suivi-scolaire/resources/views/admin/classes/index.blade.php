@extends('layouts.app')

@section('title', 'Classes')
@section('page-title', 'Gestion des classes')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('classes.create') }}" class="btn btn-primary">+ Nouvelle classe</a>
        </div>
    </div>

    <div class="grid-3">
        @forelse($classes as $classe)
        <div class="classe-card">
            <div class="classe-header">
                <div class="classe-niveau">{{ $classe->nom }}</div>
                <div style="font-size:13px; opacity:0.8; margin-top:4px">{{ $classe->niveau }} — Année {{ $anneeScolaire }}</div>
            </div>
            <div class="classe-body">
                <div class="classe-info">
                    <span>👨‍🎓 Élèves</span>
                    <span>{{ $classe->nb_eleves }}</span>
                </div>
                <div class="classe-info">
                    <span>💰 Frais</span>
                    <span>{{ number_format($classe->frais_scolarite, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="classe-info">
                    <span>📚 Matières</span>
                    <span>{{ $classe->matieres->count() }}</span>
                </div>
                <div class="matieres-list">
                    @foreach($classe->matieres as $matiere)
                    <span class="matiere-badge">{{ $matiere->nom }}</span>
                    @endforeach
                </div>
            </div>
            <div class="classe-actions">
                <a href="{{ route('classes.show', $classe) }}" class="btn btn-sm btn-blue">👁 Voir</a>
                <a href="{{ route('classes.edit', $classe) }}" class="btn btn-sm" style="background:#d97706; color:#fff;">✏️ Modifier</a>
            </div>
        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="empty-state">
                Aucune classe configurée.
                <br><br>
                <a href="{{ route('classes.create') }}" class="btn btn-primary">+ Créer la première classe</a>
            </div>
        </div>
        @endforelse
    </div>

@endsection
