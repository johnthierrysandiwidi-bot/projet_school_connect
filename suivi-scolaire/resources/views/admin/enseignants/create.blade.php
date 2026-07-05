@extends('layouts.app')

@section('title', 'Nouvel enseignant')
@section('page-title', 'Nouvel enseignant')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('enseignants.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="alert alert-info" style="max-width:600px">
        💡 L'enseignant pourra se connecter avec son email et mot de passe pour accéder aux notes de sa classe.
    </div>

    <form action="{{ route('enseignants.store') }}" method="POST" style="max-width:600px">
    @csrf

        <div class="card">
            <div class="card-header">📋 Informations de l'enseignant</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ex: SAWADOGO Abdoul">
                    @error('name')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="enseignant@ecole.bf">
                    @error('email')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Mot de passe * <span class="opt">(minimum 6 caractères)</span></label>
                    <input type="password" name="password" placeholder="••••••••">
                    @error('password')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Classe assignée *</label>
                    <select name="classe_id">
                        <option value="">-- Choisir une classe --</option>
                        @foreach($classes as $classe)
                        <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                            {{ $classe->nom }} — {{ $classe->annee_scolaire }}
                        </option>
                        @endforeach
                    </select>
                    @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Créer l'enseignant</button>
            <a href="{{ route('enseignants.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
