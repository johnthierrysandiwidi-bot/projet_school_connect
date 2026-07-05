@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres de l\'établissement')

@section('content')

    <div class="card" style="max-width:560px">
        <div class="card-header">⚙️ Paramètres généraux</div>
        <div class="card-body">

            <form action="{{ route('parametres.update') }}" method="POST"
                  data-confirm="Enregistrer ces paramètres ? Le nom de l'établissement et/ou l'année scolaire active seront mis à jour immédiatement."
                  style="margin-top:4px">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nom de l'établissement *</label>
                    <input type="text" name="nom_ecole" value="{{ old('nom_ecole', $nomEcole) }}"
                           placeholder="Ex: École Primaire Yam Wekre">
                    @error('nom_ecole')<div class="error">{{ $message }}</div>@enderror
                    <div class="form-hint">Affiché sur les bulletins, les reçus de paiement et l'en-tête de l'application.</div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Adresse (facultatif)</label>
                        <input type="text" name="adresse_ecole" value="{{ old('adresse_ecole', $adresseEcole) }}"
                               placeholder="Ex: Ouagadougou, Burkina Faso">
                        @error('adresse_ecole')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Téléphone (facultatif)</label>
                        <input type="text" name="telephone_ecole" value="{{ old('telephone_ecole', $telephoneEcole) }}"
                               placeholder="Ex: +226 70 00 00 00">
                        @error('telephone_ecole')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="form-hint" style="margin-top:-8px; margin-bottom:14px;">Si renseignées, ces coordonnées apparaissent aussi dans l'en-tête des bulletins et reçus PDF.</div>

                <hr style="border:none; border-top:1px solid var(--color-border); margin:18px 0;">

                <div class="info-row">
                    <span style="color:var(--color-text-muted)">Année actuellement utilisée par l'application</span>
                    <strong style="color:var(--color-primary); font-size:18px">{{ $anneeActive }}</strong>
                </div>

                <div class="form-group" style="margin-top:14px">
                    <label>Nouvelle année scolaire active *</label>
                    <input type="text" name="annee_scolaire_active" value="{{ old('annee_scolaire_active', $anneeActive) }}"
                           placeholder="Ex: 2026-2027" list="annees-connues">
                    <datalist id="annees-connues">
                        @foreach($anneesConnues as $annee)
                        <option value="{{ $annee }}"></option>
                        @endforeach
                    </datalist>
                    @error('annee_scolaire_active')<div class="error">{{ $message }}</div>@enderror
                    <div class="form-hint">Format AAAA-AAAA, par exemple 2026-2027.</div>
                </div>

                <button type="submit" class="btn btn-primary">✅ Enregistrer les paramètres</button>
            </form>

        </div>
    </div>

    <div class="alert alert-info" style="max-width:560px; margin-top:16px;">
        <span>
            💡 Changer l'année active prend effet immédiatement, sans relancer le
            serveur. Pour préparer la rentrée suivante (créer les classes et
            promouvoir les élèves), utilise plutôt
            <a href="{{ route('passage-annee.index') }}">Passage à l'année suivante</a>.
        </span>
    </div>

@endsection
