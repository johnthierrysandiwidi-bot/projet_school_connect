@extends('layouts.app')

@section('title', 'Reçu ' . $paiement->reference)
@section('page-title', 'Reçu de paiement')

@section('content')

    <div class="page-header no-print">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('paiements.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:560px; margin:0 auto;">

        <div style="background:var(--color-primary); color:#fff; padding:24px; text-align:center;">
            <h2 style="color:#fff; font-size:20px;">🏫 {{ config('app.nom_ecole') }}</h2>
            <p style="opacity:.85; font-size:13px; margin-top:4px;">Reçu de paiement des frais de scolarité</p>
        </div>

        <div style="background:var(--color-success-bg); border-bottom:2px dashed var(--color-border); padding:14px 24px; text-align:center; font-size:17px; font-weight:700; color:var(--color-success); letter-spacing:1px;">
            {{ $paiement->reference }}
        </div>

        <div class="card-body">

            <div class="info-row">
                <span style="color:var(--color-text-muted)">Élève</span>
                <strong>{{ $paiement->eleve->nom }} {{ $paiement->eleve->prenom }}</strong>
            </div>
            <div class="info-row">
                <span style="color:var(--color-text-muted)">Matricule</span>
                <strong>{{ $paiement->eleve->matricule }}</strong>
            </div>
            <div class="info-row">
                <span style="color:var(--color-text-muted)">Classe</span>
                <strong>{{ $paiement->eleve->classe->nom ?? '-' }}</strong>
            </div>
            <div class="info-row">
                <span style="color:var(--color-text-muted)">Date du paiement</span>
                <strong>{{ $paiement->date_paiement->format('d/m/Y') }}</strong>
            </div>
            <div class="info-row">
                <span style="color:var(--color-text-muted)">Mode de paiement</span>
                <strong>{{ ucfirst($paiement->mode_paiement) }}</strong>
            </div>
            <div class="info-row" style="border-bottom:none">
                <span style="color:var(--color-text-muted)">Enregistré par</span>
                <strong>{{ $paiement->user->name }}</strong>
            </div>

            <div style="background:var(--color-success-bg); border-radius:8px; padding:16px; margin-top:14px; text-align:center;">
                <div style="font-size:12.5px; color:var(--color-text-muted); margin-bottom:4px;">Montant reçu</div>
                <div style="font-size:26px; font-weight:700; color:var(--color-success);">
                    {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
                </div>
            </div>

            @php
                $frais = $paiement->eleve->classe->frais_scolarite ?? 0;
                $totalPaye = $paiement->eleve->montant_paye;
                $reste = $paiement->eleve->reste_a_payer;
            @endphp

            <div class="info-row" style="margin-top:10px">
                <span style="color:var(--color-text-muted)">Frais total</span>
                <strong>{{ number_format($frais, 0, ',', ' ') }} FCFA</strong>
            </div>
            <div class="info-row">
                <span style="color:var(--color-text-muted)">Total payé</span>
                <strong style="color:var(--color-accent)">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</strong>
            </div>

            <div class="alert {{ $reste == 0 ? 'alert-success' : 'alert-danger' }}" style="margin:14px 0 0; justify-content:center; text-align:center;">
                @if($reste == 0)
                    ✅ Compte soldé — Aucun reste à payer
                @else
                    ⚠️ Reste à payer : {{ number_format($reste, 0, ',', ' ') }} FCFA
                @endif
            </div>

        </div>

        <div style="padding:14px 24px; background:#f8fafc; border-top:1px solid var(--color-border); text-align:center; font-size:12px; color:var(--color-text-muted);">
            Reçu généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <div class="form-actions no-print" style="max-width:560px; margin:18px auto 0; border-top:none;">
        <a href="{{ route('paiements.recu-pdf', $paiement) }}" class="btn btn-accent">📄 Télécharger PDF</a>
        <a href="{{ route('paiements.create', ['eleve_id' => $paiement->eleve_id]) }}" class="btn btn-primary">💰 Nouveau paiement</a>
        <a href="{{ route('eleves.show', $paiement->eleve) }}" class="btn btn-outline">👁 Dossier élève</a>
    </div>

@endsection
