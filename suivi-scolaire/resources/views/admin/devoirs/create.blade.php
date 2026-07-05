@extends('layouts.app')

@section('title', 'Composer un devoir')
@section('page-title', 'Composer un devoir')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('devoirs.index') }}" class="btn btn-outline">← Retour</a>
        </div>
    </div>

    <form action="{{ route('devoirs.store') }}" method="POST" style="max-width:680px">
    @csrf

        <div class="card">
            <div class="card-header">📓 Détails du devoir</div>
            <div class="card-body">

                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre" value="{{ old('titre') }}" placeholder="Ex: Exercices de conjugaison — leçon 4">
                    @error('titre')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Description <span class="opt">(optionnel)</span></label>
                    <textarea name="description" rows="3" placeholder="Consignes, pages du manuel, exercices à faire...">{{ old('description') }}</textarea>
                    @error('description')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-grid">
                    @if($classes->count() === 1)
                        <input type="hidden" name="classe_id" value="{{ $classes->first()->id }}">
                        <div class="form-group">
                            <label>Classe</label>
                            <input type="text" value="{{ $classes->first()->nom }}" disabled>
                        </div>
                    @else
                        <div class="form-group">
                            <label>Classe *</label>
                            <select name="classe_id" id="classe_id">
                                <option value="">-- Choisir --</option>
                                @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ old('classe_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                                @endforeach
                            </select>
                            @error('classe_id')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Matière *</label>
                        <select name="matiere_id" id="matiere_id">
                            <option value="">-- Choisir --</option>
                            @foreach($matieres as $m)
                            <option value="{{ $m->id }}" data-classe="{{ $m->classe_id }}"
                                    {{ old('matiere_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('matiere_id')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Date du devoir *</label>
                        <input type="date" name="date_devoir" value="{{ old('date_devoir', date('Y-m-d')) }}">
                        @error('date_devoir')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Date limite <span class="opt">(optionnel)</span></label>
                        <input type="date" name="date_limite" value="{{ old('date_limite') }}">
                        @error('date_limite')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Trimestre *</label>
                    <select name="trimestre">
                        <option value="1" {{ old('trimestre', 1) == 1 ? 'selected' : '' }}>Trimestre 1</option>
                        <option value="2" {{ old('trimestre') == 2 ? 'selected' : '' }}>Trimestre 2</option>
                        <option value="3" {{ old('trimestre') == 3 ? 'selected' : '' }}>Trimestre 3</option>
                    </select>
                    @error('trimestre')<div class="error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-row">
                        <input type="hidden" name="noter" value="0">
                        <input type="checkbox" name="noter" id="noter" value="1" {{ old('noter', '1') ? 'checked' : '' }}>
                        <label for="noter" style="margin:0; cursor:pointer">
                            Ce devoir est noté <span class="opt">(décocher pour une simple consigne sans note)</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-actions" style="border-top:none; padding-top:0;">
            <button type="submit" class="btn btn-primary">✅ Créer le devoir</button>
            <a href="{{ route('devoirs.index') }}" class="btn btn-outline">Annuler</a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
// Filtre la liste des matières selon la classe sélectionnée (si plusieurs classes).
const classeSelect = document.getElementById('classe_id');
const matiereSelect = document.getElementById('matiere_id');

function filtrerMatieres() {
    if (!classeSelect || !matiereSelect) return;
    const classeId = classeSelect.value;
    [...matiereSelect.options].forEach(option => {
        if (!option.dataset.classe) return; // option "-- Choisir --"
        option.hidden = classeId !== '' && option.dataset.classe !== classeId;
    });
}

if (classeSelect) {
    classeSelect.addEventListener('change', filtrerMatieres);
    filtrerMatieres();
}
</script>
@endpush
