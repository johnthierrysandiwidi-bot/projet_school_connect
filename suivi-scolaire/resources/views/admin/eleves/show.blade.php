@extends('layouts.app')

@section('title', 'Dossier élève')
@section('page-title', 'Dossier de '.$eleve->prenom.' '.$eleve->nom)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('eleves.edit', $eleve) }}" class="btn btn-light">✏️ Modifier</a>
            <a href="{{ route('eleves.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    {{-- Informations élève --}}
    <div class="card">
        <div class="card-header">👨‍🎓 Informations de l'élève</div>
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:18px; margin-bottom:18px;">
                @if($eleve->photo_url)
                    <img src="{{ $eleve->photo_url }}" class="avatar-lg" alt="">
                @else
                    <div class="avatar-lg" style="display:flex; align-items:center; justify-content:center; color:var(--color-primary); font-weight:700; font-size:28px;">
                        {{ strtoupper(substr($eleve->prenom, 0, 1) . substr($eleve->nom, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div style="font-size:18px; font-weight:700; color:var(--color-text-main, #1e293b);">
                        {{ $eleve->prenom }} {{ $eleve->nom }}
                    </div>
                    <div style="color:var(--color-text-muted)">{{ $eleve->matricule }}</div>
                </div>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Matricule</label>
                    <p>{{ $eleve->matricule }}</p>
                </div>
                <div class="info-item">
                    <label>Classe</label>
                    <p><span class="badge badge-blue">{{ $eleve->classe->nom ?? '-' }}</span></p>
                </div>
                <div class="info-item">
                    <label>Nom complet</label>
                    <p>{{ $eleve->nom }} {{ $eleve->prenom }}</p>
                </div>
                <div class="info-item">
                    <label>Date de naissance</label>
                    <p>{{ $eleve->date_naissance->format('d/m/Y') }}</p>
                </div>
                <div class="info-item">
                    <label>Sexe</label>
                    <p>{{ $eleve->sexe === 'M' ? '♂ Masculin' : '♀ Féminin' }}</p>
                </div>
                <div class="info-item">
                    <label>Lieu de naissance</label>
                    <p>{{ $eleve->lieu_naissance ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Informations parent --}}
    <div class="card">
        <div class="card-header">👨‍👩‍👦 Parent / Tuteur</div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Nom complet</label>
                    <p>{{ $eleve->parent_prenom }} {{ $eleve->parent_nom }}</p>
                </div>
                <div class="info-item">
                    <label>Lien</label>
                    <p>{{ ucfirst($eleve->parent_lien) }}</p>
                </div>
                <div class="info-item">
                    <label>Téléphone</label>
                    <p>{{ $eleve->parent_telephone }}</p>
                </div>
                <div class="info-item">
                    <label>Adresse</label>
                    <p>{{ $eleve->parent_adresse ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Situation financière --}}
    <div class="card">
        <div class="card-header">
            💰 Situation financière
            <a href="{{ route('paiements.create', ['eleve_id' => $eleve->id]) }}" class="btn btn-primary btn-sm">+ Enregistrer un paiement</a>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Frais total</label>
                    <p>{{ number_format($eleve->classe->frais_scolarite ?? 0, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="info-item">
                    <label>Montant payé</label>
                    <p style="color:#059669">{{ number_format($eleve->montant_paye, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="info-item">
                    <label>Reste à payer</label>
                    <p style="color:{{ $eleve->reste_a_payer > 0 ? '#b91c1c' : '#059669' }}">
                        {{ number_format($eleve->reste_a_payer, 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="info-item">
                    <label>Statut</label>
                    <p>
                        @if($eleve->reste_a_payer == 0)
                            <span class="badge badge-green">✅ Soldé</span>
                        @else
                            <span class="badge badge-red">⚠️ Impayé</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
