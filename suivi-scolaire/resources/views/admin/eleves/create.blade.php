@extends('layouts.app')

@section('title', 'Inscrire un élève')
@section('page-title', 'Inscrire un élève')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('eleves.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('eleves.store') }}" method="POST" enctype="multipart/form-data" style="max-width:820px">
    @csrf

        {{-- Informations de l'élève --}}
        <div class="card">
            <div class="card-header">👨‍🎓 Informations de l'élève</div>
            <div class="card-body">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" placeholder="SAWADOGO">
                        @error('nom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" placeholder="Abdoul">
                        @error('prenom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Date de naissance *</label>
                        <input type="date" name="date_naissance" value="{{ old('date_naissance') }}">
                        @error('date_naissance')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance') }}" placeholder="Ouagadougou">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Sexe *</label>
                        <select name="sexe">
                            <option value="">-- Choisir --</option>
                            <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Classe *</label>
                        <select name="classe_id">
                            <option value="">-- Choisir la classe --</option>
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ old('classe_id') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

            </div>
        </div>

        {{-- Informations du parent --}}
        <div class="card">
            <div class="card-header">👨‍👩‍👦 Informations du parent / tuteur</div>
            <div class="card-body">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom du parent *</label>
                        <input type="text" name="parent_nom" value="{{ old('parent_nom') }}" placeholder="SAWADOGO">
                        @error('parent_nom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Prénom du parent *</label>
                        <input type="text" name="parent_prenom" value="{{ old('parent_prenom') }}" placeholder="Moussa">
                        @error('parent_prenom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="parent_telephone" value="{{ old('parent_telephone') }}" placeholder="70 00 00 00">
                        @error('parent_telephone')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Lien de parenté *</label>
                        <select name="parent_lien">
                            <option value="père" {{ old('parent_lien') == 'père' ? 'selected' : '' }}>Père</option>
                            <option value="mère" {{ old('parent_lien') == 'mère' ? 'selected' : '' }}>Mère</option>
                            <option value="tuteur" {{ old('parent_lien') == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                            <option value="autre" {{ old('parent_lien') == 'autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="parent_adresse" value="{{ old('parent_adresse') }}" placeholder="Secteur 15, Ouagadougou">
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Inscrire l'élève</button>
            <a href="{{ route('eleves.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection
