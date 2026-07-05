@extends('layouts.app')

@section('title', 'Modifier ' . $classe->nom)
@section('page-title', 'Modifier — Classe ' . $classe->nom)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('classes.show', $classe) }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('classes.update', $classe) }}" method="POST" style="max-width:680px">
    @csrf
    @method('PUT')

        {{-- Informations --}}
        <div class="card">
            <div class="card-header">📋 Informations de la classe {{ $classe->nom }} <span style="font-size:13px; opacity:.7;">({{ $classe->niveau }})</span></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nom de la classe *</label>
                    <input type="text" name="nom" value="{{ old('nom', $classe->nom) }}" placeholder="ex: CP1 ou CP1 A">
                    <span class="form-hint">Utile pour distinguer plusieurs classes du même niveau (ex. « CP1 A », « CP1 B »).</span>
                    @error('nom')<div class="error">{{ $message }}</div>@enderror
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Frais de scolarité (FCFA) *</label>
                        <input type="number" name="frais_scolarite"
                               value="{{ old('frais_scolarite', $classe->frais_scolarite) }}"
                               min="0">
                        @error('frais_scolarite')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Capacité maximale *</label>
                        <input type="number" name="capacite_max"
                               value="{{ old('capacite_max', $classe->capacite_max) }}"
                               min="1" max="100">
                        @error('capacite_max')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Matières existantes --}}
        <div class="card">
            <div class="card-header">📚 Matières existantes</div>
            <div class="card-body">
                @forelse($classe->matieres as $matiere)
                <div class="matiere-row">
                    <div class="form-group">
                        <input type="text"
                               name="matieres[{{ $matiere->id }}][nom]"
                               value="{{ $matiere->nom }}">
                    </div>
                    <div class="form-group coeff">
                        <input type="number"
                               name="matieres[{{ $matiere->id }}][coefficient]"
                               value="{{ $matiere->coefficient }}"
                               min="0.5" max="5" step="0.5">
                    </div>
                    <div class="form-group bareme">
                        <select name="matieres[{{ $matiere->id }}][bareme]">
                            <option value="10" {{ $matiere->bareme == 10 ? 'selected' : '' }}>Sur 10</option>
                            <option value="20" {{ $matiere->bareme == 20 ? 'selected' : '' }}>Sur 20</option>
                        </select>
                    </div>
                    {{-- Le bouton soumet le formulaire "delete-matiere-{id}" défini
                         plus bas (hors de ce formulaire-ci) via l'attribut HTML5
                         "form" : on ne peut pas imbriquer un <form> dans un autre. --}}
                    <button type="submit" form="delete-matiere-{{ $matiere->id }}"
                            class="remove-btn" title="Supprimer cette matière">✕</button>
                </div>
                @empty
                <p class="form-hint">Aucune matière enregistrée pour cette classe pour le moment.</p>
                @endforelse
            </div>
        </div>

        {{-- Nouvelles matières --}}
        <div class="card">
            <div class="card-header">➕ Ajouter des matières</div>
            <div class="card-body">
                <div id="nouvelles-matieres"></div>
                <button type="button" class="btn btn-accent btn-sm" onclick="ajouterMatiere()">
                    + Ajouter une matière
                </button>
            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Enregistrer les modifications</button>
            <a href="{{ route('classes.show', $classe) }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

    {{-- Formulaires de suppression de matière, en dehors du formulaire
         principal (un <form> ne peut pas en contenir un autre). Chaque
         bouton "✕" ci-dessus les déclenche via l'attribut HTML "form". --}}
    @foreach($classe->matieres as $matiere)
    <form id="delete-matiere-{{ $matiere->id }}"
          action="{{ route('matieres.destroy', $matiere) }}" method="POST"
          data-confirm="Supprimer la matière « {{ $matiere->nom }} » ?" style="display:none">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

@endsection

@push('scripts')
<script>
let compteur = 0;

function ajouterMatiere() {
    const container = document.getElementById('nouvelles-matieres');
    const row = document.createElement('div');
    row.className = 'matiere-row';
    row.innerHTML = `
        <div class="form-group">
            <input type="text" name="nouvelles_matieres[${compteur}][nom]" placeholder="Nom de la matière">
        </div>
        <div class="form-group coeff">
            <input type="number" name="nouvelles_matieres[${compteur}][coefficient]" placeholder="Coeff" value="1" min="0.5" max="5" step="0.5">
        </div>
        <div class="form-group bareme">
            <select name="nouvelles_matieres[${compteur}][bareme]">
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
