@extends('layouts.app')

@section('title', 'Modifier un élève')
@section('page-title', 'Modifier — '.$eleve->prenom.' '.$eleve->nom)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('eleves.show', $eleve) }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <div class="alert alert-success"><span>🎓 Matricule : <strong>{{ $eleve->matricule }}</strong></span></div>

    <form action="{{ route('eleves.update', $eleve) }}" method="POST" enctype="multipart/form-data" style="max-width:820px">
    @csrf
    @method('PUT')

        {{-- Informations de l'élève --}}
        <div class="card">
            <div class="card-header">👨‍🎓 Informations de l'élève</div>
            <div class="card-body">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom', $eleve->nom) }}" placeholder="SAWADOGO">
                        @error('nom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $eleve->prenom) }}" placeholder="Abdoul">
                        @error('prenom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Date de naissance *</label>
                        <input type="date" name="date_naissance"
                               value="{{ old('date_naissance', $eleve->date_naissance->format('Y-m-d')) }}">
                        @error('date_naissance')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance', $eleve->lieu_naissance) }}" placeholder="Ouagadougou">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Sexe *</label>
                        <select name="sexe">
                            <option value="M" {{ old('sexe', $eleve->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe', $eleve->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Classe *</label>
                        <select name="classe_id">
                            @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ old('classe_id', $eleve->classe_id) == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Statut *</label>
                        <select name="statut">
                            <option value="actif" {{ old('statut', $eleve->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="redoublant" {{ old('statut', $eleve->statut) == 'redoublant' ? 'selected' : '' }}>Redoublant</option>
                            <option value="transfere" {{ old('statut', $eleve->statut) == 'transfere' ? 'selected' : '' }}>Transféré</option>
                            <option value="exclu" {{ old('statut', $eleve->statut) == 'exclu' ? 'selected' : '' }}>Exclu</option>
                            <option value="diplome" {{ old('statut', $eleve->statut) == 'diplome' ? 'selected' : '' }}>Diplômé (fin du primaire)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Photo</label>
                        @if($eleve->photo_url)
                            <div style="margin-bottom:8px">
                                <img src="{{ $eleve->photo_url }}" class="avatar-lg" alt="">
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*">
                        <div class="form-hint">Laisser vide pour conserver la photo actuelle.</div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Informations du parent --}}
        <div class="card">
            <div class="card-header">👨‍👩‍👦 Parent / Tuteur</div>
            <div class="card-body">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nom du parent *</label>
                        <input type="text" name="parent_nom" value="{{ old('parent_nom', $eleve->parent_nom) }}">
                        @error('parent_nom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Prénom du parent *</label>
                        <input type="text" name="parent_prenom" value="{{ old('parent_prenom', $eleve->parent_prenom) }}">
                        @error('parent_prenom')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="parent_telephone" value="{{ old('parent_telephone', $eleve->parent_telephone) }}">
                        @error('parent_telephone')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Lien de parenté *</label>
                        <select name="parent_lien">
                            <option value="père" {{ old('parent_lien', $eleve->parent_lien) == 'père' ? 'selected' : '' }}>Père</option>
                            <option value="mère" {{ old('parent_lien', $eleve->parent_lien) == 'mère' ? 'selected' : '' }}>Mère</option>
                            <option value="tuteur" {{ old('parent_lien', $eleve->parent_lien) == 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                            <option value="autre" {{ old('parent_lien', $eleve->parent_lien) == 'autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="parent_adresse" value="{{ old('parent_adresse', $eleve->parent_adresse) }}" placeholder="Secteur 15, Ouagadougou">
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer les modifications</button>
            <a href="{{ route('eleves.show', $eleve) }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

    {{-- Formulaire de suppression --}}
    <form action="{{ route('eleves.destroy', $eleve) }}" method="POST"
          data-confirm="⚠️ Voulez-vous vraiment supprimer cet élève ? Cette action est irréversible !"
          style="margin-top:14px; max-width:820px">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">🗑️ Supprimer cet élève</button>
    </form>

@endsection
