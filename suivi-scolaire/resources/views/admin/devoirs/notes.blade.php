@extends('layouts.app')

@section('title', 'Notes — ' . $devoir->titre)
@section('page-title', 'Saisie des notes du devoir')
@section('page-subtitle', $devoir->titre . ' — ' . $devoir->matiere->nom)

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('devoirs.index', ['trimestre' => $devoir->trimestre]) }}" class="btn btn-outline">← Retour au cahier de notes</a>
        </div>
    </div>

    <form action="{{ route('devoirs.notes.store', $devoir) }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <span>📝 {{ $devoir->titre }} — {{ $devoir->classe->nom }} — {{ $devoir->date_devoir->format('d/m/Y') }}</span>
            <button type="submit" class="btn btn-primary btn-sm">💾 Enregistrer les notes</button>
        </div>

        <div class="table-responsive">
            <table class="notes-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Note / {{ $devoir->matiere->bareme }}</th>
                        <th>Remarque <span class="opt">(optionnel)</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eleves as $eleve)
                    @php $note = $notes[$eleve->id] ?? null; @endphp
                    <tr>
                        <td>{{ $eleve->prenom }} {{ $eleve->nom }}</td>
                        <td>
                            <input type="number"
                                   class="note-input"
                                   name="notes[{{ $eleve->id }}]"
                                   value="{{ $note ? $note->valeur : '' }}"
                                   min="0" max="{{ $devoir->matiere->bareme }}" step="0.5"
                                   placeholder="—">
                        </td>
                        <td>
                            <input type="text"
                                   name="remarques[{{ $eleve->id }}]"
                                   value="{{ $note ? $note->remarque : '' }}"
                                   placeholder="Ex: Très bon travail">
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="3">Aucun élève inscrit dans cette classe.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 22px; border-top:1px solid var(--color-border); text-align:right">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les notes</button>
        </div>
    </div>
    </form>

@endsection
