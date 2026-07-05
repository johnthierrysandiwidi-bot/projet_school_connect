@extends('layouts.app')

@section('title', 'Présences')
@section('page-title', 'Feuille de présence')
@section('page-subtitle', $classe ? "{$classe->nom} — " . \Carbon\Carbon::parse($date)->format('d/m/Y') : null)

@section('content')

    {{-- Filtres --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('absences.index') }}">
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
                        <label>Date</label>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" max="{{ date('Y-m-d') }}">
                    </div>
                    <a href="{{ route('absences.historique', ['classe_id' => $classe?->id]) }}" class="btn btn-outline">
                        📋 Historique
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($classe && $eleves->count() > 0)

    <form action="{{ route('absences.store') }}" method="POST">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">
    <input type="hidden" name="date_absence" value="{{ $date }}">

    <div class="card">
        <div class="card-header">
            <span>📋 {{ $classe->nom }} — {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
            <button type="submit" class="btn btn-primary btn-sm">💾 Enregistrer les présences</button>
        </div>

        <div class="table-responsive">
            <table class="notes-table">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Absent(e)</th>
                        <th>Justifiée</th>
                        <th>Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eleves as $eleve)
                    @php $absence = $absences->get($eleve->id); @endphp
                    <tr>
                        <td>{{ $eleve->prenom }} {{ $eleve->nom }}</td>
                        <td>
                            <input type="checkbox"
                                   class="absence-toggle"
                                   data-target="absence-row-{{ $eleve->id }}"
                                   {{ $absence ? 'checked' : '' }}>
                        </td>
                        <td id="justifiee-cell-{{ $eleve->id }}" class="{{ $absence ? '' : 'd-none' }}" data-row="absence-row-{{ $eleve->id }}">
                            <input type="checkbox" name="absences[{{ $eleve->id }}][justifiee]" value="1" {{ $absence && $absence->justifiee ? 'checked' : '' }}>
                        </td>
                        <td id="motif-cell-{{ $eleve->id }}" class="{{ $absence ? '' : 'd-none' }}" data-row="absence-row-{{ $eleve->id }}">
                            <input type="text" name="absences[{{ $eleve->id }}][motif]" value="{{ $absence?->motif }}" placeholder="Ex: Maladie">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:16px 22px; border-top:1px solid var(--color-border); text-align:right">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les présences</button>
        </div>
    </div>
    </form>

    @elseif($classe)
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

@push('scripts')
<script>
// Affiche/masque les colonnes "Justifiée"/"Motif" selon la case "Absent(e)".
document.querySelectorAll('.absence-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const target = this.dataset.target;
        document.querySelectorAll(`[data-row="${target}"]`).forEach(cell => {
            cell.classList.toggle('d-none', !this.checked);
            // Vide les champs masqués pour ne pas envoyer de données fantômes.
            if (!this.checked) {
                cell.querySelectorAll('input[type=text]').forEach(i => i.value = '');
                cell.querySelectorAll('input[type=checkbox]').forEach(i => i.checked = false);
            }
        });
    });
});
</script>
@endpush
