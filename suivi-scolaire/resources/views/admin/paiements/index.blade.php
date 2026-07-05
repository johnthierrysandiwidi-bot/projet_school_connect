@extends('layouts.app')

@section('title', 'Paiements')
@section('page-title', 'Paiements')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('paiements.create') }}" class="btn btn-primary">+ Nouveau paiement</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>📋 Liste des paiements ({{ $paiements->total() }})</span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paiements as $p)
                    <tr>
                        <td style="font-family:monospace; color:var(--color-text-muted); font-size:12px">{{ $p->reference }}</td>
                        <td><strong>{{ $p->eleve->nom }} {{ $p->eleve->prenom }}</strong></td>
                        <td><span class="badge badge-blue">{{ $p->eleve->classe->nom ?? '-' }}</span></td>
                        <td style="color:var(--color-accent); font-weight:bold">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($p->mode_paiement) }}</td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('paiements.recu', $p) }}" class="btn btn-outline btn-sm">🧾 Reçu</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            Aucun paiement enregistré.
                            <br><br>
                            <a href="{{ route('paiements.create') }}" class="btn btn-primary">+ Enregistrer un paiement</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paiements->hasPages())
        <div style="padding:6px 22px 18px;">
            {{ $paiements->links() }}
        </div>
        @endif
    </div>

@endsection
