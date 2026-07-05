@extends('layouts.app')

@section('title', 'Nouvelle classe')
@section('page-title', 'Nouvelle classe')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('classes.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('classes.store') }}" method="POST" style="max-width:680px">
    @csrf

        {{-- Informations --}}
        <div class="card">
            <div class="card-header">📋 Informations de la classe</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Niveau *</label>
                    <select name="niveau">
                        <option value="">-- Choisir le niveau --</option>
                        @foreach($niveaux as $niveau)
                            <option value="{{ $niveau }}" {{ old('niveau') == $niveau ? 'selected' : '' }}>{{ $niveau }}</option>
                        @endforeach
                    </select>
                    @error('niveau')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Nom de la classe *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" placeholder="ex: CP1 ou CP1 A">
                    <span class="form-hint">S'il existe déjà une classe pour ce niveau cette année, donne un nom distinct (ex. « CP1 A » et « CP1 B ») pour les différencier.</span>
                    @error('nom')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Frais de scolarité (FCFA) *</label>
                        <input type="number" name="frais_scolarite" value="{{ old('frais_scolarite') }}" min="0" placeholder="150000">
                        @error('frais_scolarite')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Capacité maximale *</label>
                        <input type="number" name="capacite_max" value="{{ old('capacite_max', 40) }}" min="1" max="100">
                        @error('capacite_max')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Matières --}}
        <div class="card">
            <div class="card-header">📚 Matières enseignées (facultatif)</div>
            <div class="card-body">
                <div id="matieres-container"></div>
                <button type="button" class="btn btn-accent btn-sm" onclick="ajouterMatiere()">+ Ajouter une matière</button>
            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Créer la classe</button>
            <a href="{{ route('classes.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
let compteur = 0;

function ajouterMatiere() {
    const container = document.getElementById('matieres-container');
    const row = document.createElement('div');
    row.className = 'matiere-row';
    row.innerHTML = `
        <div class="form-group">
            <input type="text" name="matieres[${compteur}][nom]" placeholder="Nom de la matière (ex: Français)">
        </div>
        <div class="form-group coeff">
            <input type="number" name="matieres[${compteur}][coefficient]" placeholder="Coeff" value="1" min="0.5" max="5" step="0.5">
        </div>
        <div class="form-group bareme">
            <select name="matieres[${compteur}][bareme]">
                <option value="10" selected>Sur 10</option>
                <option value="20">Sur 20</option>
            </select>
        </div>
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✕</button>
    `;
    container.appendChild(row);
    compteur++;
}
</script>
@endpush
