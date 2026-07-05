@extends('layouts.app')

@section('title', 'Modifier ' . $parent->name)
@section('page-title', 'Modifier — ' . $parent->name)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('parents.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="alert alert-warning" style="max-width:680px">
        ⚠️ Laissez le mot de passe vide pour ne pas le modifier.
    </div>

    <form action="{{ route('parents.update', $parent) }}" method="POST" style="max-width:680px">
    @csrf
    @method('PUT')

        <div class="card">
            <div class="card-header">📋 Informations du parent</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $parent->name) }}">
                    @error('name')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email', $parent->email) }}">
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe <span class="opt">(laisser vide pour ne pas changer)</span></label>
                    <input type="password" name="password" placeholder="••••••••">
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_active" id="is_active"
                               {{ old('is_active', $parent->is_active) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0; cursor:pointer">Compte actif</label>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">👨‍🎓 Enfant(s) associé(s) à ce compte *</div>
            <div class="card-body">
                @error('enfants')<div class="error" style="margin-bottom:10px">{{ $message }}</div>@enderror

                <div style="max-height:280px; overflow-y:auto; border:1px solid var(--color-border); border-radius:8px; padding:10px;">
                    @forelse($eleves as $eleve)
                    @php $selectionnes = old('enfants', $enfantsActuels); @endphp
                    <div class="checkbox-row">
                        <input type="checkbox" name="enfants[]" id="eleve-{{ $eleve->id }}" value="{{ $eleve->id }}"
                               {{ in_array($eleve->id, $selectionnes) ? 'checked' : '' }}>
                        <label for="eleve-{{ $eleve->id }}" style="margin:0; cursor:pointer">
                            {{ $eleve->nom }} {{ $eleve->prenom }}
                            <span class="form-hint" style="display:inline">— {{ $eleve->classe->nom ?? '-' }} — {{ $eleve->matricule }}</span>
                        </label>
                    </div>
                    @empty
                    <p class="form-hint">Aucun élève inscrit pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer les modifications</button>
            <a href="{{ route('parents.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
