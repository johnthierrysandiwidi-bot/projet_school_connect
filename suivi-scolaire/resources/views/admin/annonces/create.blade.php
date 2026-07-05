@extends('layouts.app')

@section('title', 'Publier une annonce')
@section('page-title', 'Publier une annonce')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('annonces.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('annonces.store') }}" method="POST" style="max-width:680px">
    @csrf

        <div class="card">
            <div class="card-header">📢 Détails de l'annonce</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre" value="{{ old('titre') }}" placeholder="Ex: Réunion des parents d'élèves">
                    @error('titre')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Contenu *</label>
                    <textarea name="contenu" rows="4" placeholder="Détails de l'annonce...">{{ old('contenu') }}</textarea>
                    @error('contenu')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="type">
                            <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>📢 Information générale</option>
                            <option value="examen" {{ old('type') == 'examen' ? 'selected' : '' }}>📝 Examen</option>
                            <option value="reunion" {{ old('type') == 'reunion' ? 'selected' : '' }}>👥 Réunion</option>
                            <option value="paiement" {{ old('type') == 'paiement' ? 'selected' : '' }}>💰 Échéance de paiement</option>
                        </select>
                        @error('type')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Date de publication *</label>
                        <input type="date" name="date_publication" value="{{ old('date_publication', date('Y-m-d')) }}">
                        @error('date_publication')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Destinataire</label>
                    @if($classes->count() === 1)
                        <input type="hidden" name="classe_id" value="{{ $classes->first()->id }}">
                        <input type="text" value="Classe {{ $classes->first()->nom }} uniquement" disabled>
                        <div class="form-hint">En tant qu'enseignant, vous ne pouvez publier que pour votre classe.</div>
                    @else
                        <select name="classe_id">
                            <option value="">🏫 Toute l'école</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ old('classe_id') == $c->id ? 'selected' : '' }}>
                                Classe {{ $c->nom }} uniquement
                            </option>
                            @endforeach
                        </select>
                        @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                    @endif
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Publier l'annonce</button>
            <a href="{{ route('annonces.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
