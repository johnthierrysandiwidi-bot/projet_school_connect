@extends('layouts.app')

@section('title', 'Modifier ' . $enseignant->name)
@section('page-title', 'Modifier — ' . $enseignant->name)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('enseignants.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="alert alert-warning" style="max-width:600px">
        ⚠️ Laissez le mot de passe vide pour ne pas le modifier.
    </div>

    <form action="{{ route('enseignants.update', $enseignant) }}" method="POST" style="max-width:600px">
    @csrf
    @method('PUT')

        <div class="card">
            <div class="card-header">📋 Informations de l'enseignant</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $enseignant->name) }}" placeholder="Ex: SAWADOGO Abdoul">
                    @error('name')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email', $enseignant->email) }}" placeholder="enseignant@ecole.bf">
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe <span class="opt">(laisser vide pour ne pas changer)</span></label>
                    <input type="password" name="password" placeholder="••••••••">
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Classe assignée *</label>
                    <select name="classe_id">
                        <option value="">-- Choisir une classe --</option>
                        @foreach($classes as $classe)
                        <option value="{{ $classe->id }}" {{ old('classe_id', $enseignant->classe_id) == $classe->id ? 'selected' : '' }}>
                            {{ $classe->nom }} — {{ $classe->annee_scolaire }}
                        </option>
                        @endforeach
                    </select>
                    @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_active" id="is_active"
                               {{ old('is_active', $enseignant->is_active) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0; cursor:pointer">Compte actif</label>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer les modifications</button>
            <a href="{{ route('enseignants.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

    <form action="{{ route('enseignants.destroy', $enseignant) }}" method="POST"
          data-confirm="Supprimer l'enseignant {{ $enseignant->name }} ?" style="max-width:600px; margin-top:14px">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-block">🗑️ Supprimer cet enseignant</button>
    </form>

@endsection
