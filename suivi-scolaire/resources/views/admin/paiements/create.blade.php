@extends('layouts.app')

@section('title', 'Nouveau paiement')
@section('page-title', 'Nouveau paiement')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('paiements.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('paiements.store') }}" method="POST" style="max-width:680px">
    @csrf

        <div class="card">
            <div class="card-header">📋 Détails du paiement</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Élève *</label>
                    <select name="eleve_id" id="eleve_id" onchange="selectionnerEleve(this.value)">
                        <option value="">-- Sélectionner un élève --</option>
                        @foreach($eleves as $e)
                        <option value="{{ $e->id }}" {{ ($eleve && $eleve->id == $e->id) ? 'selected' : '' }}>
                            {{ $e->nom }} {{ $e->prenom }} — {{ $e->classe->nom ?? '' }} — {{ $e->matricule }}
                        </option>
                        @endforeach
                    </select>
                    @error('eleve_id')<div class="error">{{ $message }}</div>@enderror
                </div>

                @if($eleve)
                <div class="card" style="background:var(--color-success-bg); box-shadow:none; margin-bottom:16px;">
                    <div class="card-body">
                        <div class="info-row">
                            <span style="color:var(--color-text-muted)">Frais total :</span>
                            <strong>{{ number_format($eleve->classe->frais_scolarite ?? 0, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="info-row">
                            <span style="color:var(--color-text-muted)">Déjà payé :</span>
                            <strong style="color:var(--color-accent)">{{ number_format($eleve->montant_paye, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="info-row" style="border-bottom:none">
                            <span style="color:var(--color-text-muted)">Reste à payer :</span>
                            <strong style="color:var(--color-danger)">{{ number_format($eleve->reste_a_payer, 0, ',', ' ') }} FCFA</strong>
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label>Montant (FCFA) *</label>
                        <input type="number" name="montant" value="{{ old('montant') }}" placeholder="Ex: 15000" min="1">
                        @error('montant')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Date du paiement *</label>
                        <input type="date" name="date_paiement" value="{{ old('date_paiement', date('Y-m-d')) }}">
                        @error('date_paiement')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Mode de paiement *</label>
                    <select name="mode_paiement">
                        <option value="espèces" {{ old('mode_paiement') == 'espèces' ? 'selected' : '' }}>💵 Espèces</option>
                        <option value="mobile_money" {{ old('mode_paiement') == 'mobile_money' ? 'selected' : '' }}>📱 Mobile Money</option>
                        <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>🏦 Virement</option>
                        <option value="chèque" {{ old('mode_paiement') == 'chèque' ? 'selected' : '' }}>🧾 Chèque</option>
                    </select>
                    @error('mode_paiement')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Observation <span class="opt">(optionnel)</span></label>
                    <textarea name="observation" rows="2" placeholder="Note optionnelle...">{{ old('observation') }}</textarea>
                    @error('observation')<div class="error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer le paiement</button>
            <a href="{{ route('paiements.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
// Le sélecteur d'élève ne doit PAS soumettre ce formulaire (qui enregistre
// un paiement) : on recharge simplement cette même page en GET avec
// l'élève choisi, pour afficher son reste à payer avant la saisie.
function selectionnerEleve(eleveId) {
    const url = "{{ route('paiements.create') }}";
    window.location.href = eleveId ? (url + '?eleve_id=' + eleveId) : url;
}
</script>
@endpush
