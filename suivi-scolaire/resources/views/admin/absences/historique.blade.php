@extends('layouts.app')

@section('title', 'Historique des absences')
@section('page-title', 'Historique des absences')
@section('page-subtitle', $classe ? "{$classe->nom} — {$annee}" : null)

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('absences.historique') }}">
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
                    <a href="{{ route('absences.index', ['classe_id' => $classe?->id]) }}" class="btn btn-outline">
                        ✏️ Saisir les présences
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($classe)
    <div class="card">
        <div class="card-header">📋 Absences — {{ $classe->nom }}</div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Élève</th>
                        <th>Justifiée</th>
                        <th>Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences as $absence)
                    <tr>
                        <td>{{ $absence->date_absence->format('d/m/Y') }}</td>
                        <td>{{ $absence->eleve->prenom }} {{ $absence->eleve->nom }}</td>
                        <td>
                            @if($absence->justifiee)
                                <span class="badge badge-green">✅ Oui</span>
                            @else
                                <span class="badge badge-red">❌ Non</span>
                            @endif
                        </td>
                        <td>{{ $absence->motif ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="4">Aucune absence enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($absences->hasPages())
        <div style="padding:6px 22px 18px;">{{ $absences->links() }}</div>
        @endif
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
