@extends('layouts.app')

@section('title', 'Saisie des notes')
@section('page-title', 'Saisie des notes')
@section('page-subtitle', "Trimestre {$trimestre} — {$annee}")

@section('content')

    {{-- Filtres --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('notes.index') }}">
                <div class="filtre-group">
                    @if($classes->count() > 1)
                    <div class="form-group" style="margin-bottom:0">
                        <label>Classe</label>
                        <select name="classe_id" onchange="this.form.submit()">
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ ($classe && $classe->id == $c->id) ? 'selected' : '' }}>
                                {{ $c->nom }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group" style="margin-bottom:0">
                        <label>Trimestre</label>
                        <select name="trimestre" onchange="this.form.submit()">
                            <option value="1" {{ $trimestre == 1 ? 'selected' : '' }}>Trimestre 1</option>
                            <option value="2" {{ $trimestre == 2 ? 'selected' : '' }}>Trimestre 2</option>
                            <option value="3" {{ $trimestre == 3 ? 'selected' : '' }}>Trimestre 3</option>
                        </select>
                    </div>
                    <a href="{{ route('notes.classement', ['classe_id' => $classe?->id, 'trimestre' => $trimestre]) }}" class="btn btn-outline">
                        🏆 Voir le classement
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($classe && $eleves->count() > 0 && $matieres->count() > 0)

    <form action="{{ route('notes.store') }}" method="POST">
    @csrf
    <input type="hidden" name="trimestre" value="{{ $trimestre }}">
    <input type="hidden" name="annee_scolaire" value="{{ $annee }}">

    <div class="card">
        <div class="card-header">
            <span>📋 {{ $classe->nom }} — Trimestre {{ $trimestre }} — {{ $annee }}</span>
        </div>

        <div class="table-responsive">
            <table class="notes-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        @foreach($matieres as $matiere)
                        <th>
                            {{ $matiere->nom }}
                            <span class="coeff">/{{ $matiere->bareme }} — coeff. {{ $matiere->coefficient }}</span>
                        </th>
                        @endforeach
                        <th>Moyenne /10</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eleves as $eleve)
                    @php
                        $eleveNotes = $notes[$eleve->id] ?? collect();
                        $moyenne = \App\Services\MoyenneService::moyenneEleve($eleve, $trimestre, $annee);
                    @endphp
                    <tr>
                        <td>{{ $eleve->prenom }} {{ $eleve->nom }}</td>
                        @foreach($matieres as $matiere)
                        @php
                            $note = $eleveNotes[$matiere->id] ?? null;
                        @endphp
                        <td>
                            <input type="number"
                                   class="note-input"
                                   name="notes[{{ $eleve->id }}][{{ $matiere->id }}]"
                                   value="{{ $note ? $note->valeur : '' }}"
                                   min="0" max="{{ $matiere->bareme }}" step="0.5"
                                   placeholder="—">
                        </td>
                        @endforeach
                        <td>
                            @if($moyenne !== null)
                                <strong style="color:{{ $moyenne >= 5 ? '#059669' : '#b91c1c' }}">
                                    {{ $moyenne }}/10
                                </strong>
                            @else
                                <span style="color:#94a3b8">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:16px 22px; border-top:1px solid var(--color-border); text-align:right">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les notes</button>
        </div>
    </div>
    </form>

    @elseif($classe && $matieres->count() == 0)
    <div class="card">
        <div class="empty-state">
            <div class="icon">⚠️</div>
            Aucune matière configurée pour la classe {{ $classe->nom }}.
            <br><br>
            <a href="{{ route('classes.edit', $classe) }}" class="btn btn-primary">Configurer les matières</a>
        </div>
    </div>

    @elseif($classe && $eleves->count() == 0)
    <div class="card">
        <div class="empty-state">
            <div class="icon">⚠️</div>
            Aucun élève inscrit dans la classe {{ $classe->nom }}.
        </div>
    </div>
    @else
    <div class="card">
        <div class="empty-state">
            <div class="icon">📭</div>
            Aucune classe disponible.
        </div>
    </div>
    @endif

@endsection
