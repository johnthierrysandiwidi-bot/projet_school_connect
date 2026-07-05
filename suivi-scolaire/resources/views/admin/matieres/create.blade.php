@extends('layouts.app')

@section('title', 'Nouvelle matière')
@section('page-title', 'Nouvelle matière')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('matieres.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('matieres.store') }}" method="POST" style="max-width:560px">
    @csrf

        <div class="card">
            <div class="card-header">📚 Détails de la matière</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Français">
                    @error('nom')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Code <span class="opt">(optionnel)</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Ex: FR">
                    @error('code')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Classe *</label>
                        <select name="classe_id">
                            <option value="">-- Choisir --</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('classe_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                        @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Coefficient *</label>
                        <input type="number" name="coefficient" value="{{ old('coefficient', 1) }}" min="0.5" max="5" step="0.5">
                        @error('coefficient')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Barème *</label>
                    <select name="bareme">
                        <option value="10" {{ old('bareme', 10) == 10 ? 'selected' : '' }}>Sur 10</option>
                        <option value="20" {{ old('bareme') == 20 ? 'selected' : '' }}>Sur 20</option>
                    </select>
                    <div class="form-hint">Du CP1 au CE2, les matières sont généralement notées sur 10. Au CM1/CM2, certaines matières (étude de texte, problème, opération, sciences, histoire-géographie) sont notées sur 20.</div>
                    @error('bareme')<div class="error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Créer la matière</button>
            <a href="{{ route('matieres.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
