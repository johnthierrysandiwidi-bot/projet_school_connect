@extends('layouts.app')

@section('title', 'Modifier ' . $matiere->nom)
@section('page-title', 'Modifier — ' . $matiere->nom)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('matieres.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('matieres.update', $matiere) }}" method="POST" style="max-width:560px">
    @csrf
    @method('PUT')

        <div class="card">
            <div class="card-header">📚 Détails de la matière</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom', $matiere->nom) }}">
                    @error('nom')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Code <span class="opt">(optionnel)</span></label>
                    <input type="text" name="code" value="{{ old('code', $matiere->code) }}">
                    @error('code')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Classe *</label>
                        <select name="classe_id">
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('classe_id', $matiere->classe_id) == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                        @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Coefficient *</label>
                        <input type="number" name="coefficient" value="{{ old('coefficient', $matiere->coefficient) }}" min="0.5" max="5" step="0.5">
                        @error('coefficient')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Barème *</label>
                    <select name="bareme">
                        <option value="10" {{ old('bareme', $matiere->bareme) == 10 ? 'selected' : '' }}>Sur 10</option>
                        <option value="20" {{ old('bareme', $matiere->bareme) == 20 ? 'selected' : '' }}>Sur 20</option>
                    </select>
                    <div class="form-hint">⚠️ Changer le barème ne convertit pas les notes déjà saisies pour cette matière — vérifie-les si besoin.</div>
                    @error('bareme')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-row">
                       <input type="hidden" name="is_active" value="0">

                    <input type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ old('is_active', $matiere->is_active) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0; cursor:pointer">Matière active</label>
                    </div>
                    <div class="form-hint">Une matière inactive n'apparaît plus dans la saisie des notes, mais ses notes déjà saisies restent visibles.</div>
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer les modifications</button>
            <a href="{{ route('matieres.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
