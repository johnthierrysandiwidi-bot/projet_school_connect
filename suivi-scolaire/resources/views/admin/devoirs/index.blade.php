@extends('layouts.app')

@section('title', 'Cahier de notes')
@section('page-title', 'Cahier de notes')
@section('page-subtitle', $classe ? "{$classe->nom} — Trimestre {$trimestre} — {$annee}" : null)

@section('content')

    {{-- Filtres --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('devoirs.index') }}">
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
                    <a href="{{ route('devoirs.create') }}" class="btn btn-primary">
                        + Composer un devoir
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($classe)
    <div class="card">
        <div class="card-header">
            📓 Devoirs — {{ $classe->nom }} — Trimestre {{ $trimestre }} — {{ $annee }}
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Matière</th>
                        <th>Date du devoir</th>
                        <th>Date limite</th>
                        <th>Type</th>
                        <th>Progression</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoirs as $devoir)
                    <tr>
                        <td>
                            <strong>{{ $devoir->titre }}</strong>
                            @if($devoir->description)
                                <div class="form-hint">{{ \Illuminate\Support\Str::limit($devoir->description, 60) }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-blue">{{ $devoir->matiere->nom }}</span></td>
                        <td>{{ $devoir->date_devoir->format('d/m/Y') }}</td>
                        <td>{{ $devoir->date_limite?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if($devoir->noter)
                                <span class="badge badge-green">📝 Noté</span>
                            @else
                                <span class="badge badge-gray">📋 Consigne</span>
                            @endif
                        </td>
                        <td>
                            @if($devoir->noter)
                                {{ $devoir->nombre_notes }} / {{ $effectifClasse }} notés
                                @if($devoir->moyenne_classe !== null)
                                    <div class="form-hint">Moyenne : {{ $devoir->moyenne_classe }}/{{ $devoir->matiere->bareme }}</div>
                                @endif
                            @else
                                <span style="color:var(--color-text-muted)">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="cell-actions">
                                @if($devoir->noter)
                                <a href="{{ route('devoirs.notes', $devoir) }}" class="btn btn-outline btn-sm">📝 Saisir les notes</a>
                                @endif
                                <form action="{{ route('devoirs.destroy', $devoir) }}" method="POST"
                                      data-confirm="Supprimer le devoir « {{ $devoir->titre }} » ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            Aucun devoir composé pour ce trimestre.
                            <br><br>
                            <a href="{{ route('devoirs.create') }}" class="btn btn-primary">+ Composer un devoir</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="card">
        <div class="empty-state">
            <div class="icon">📭</div>
            Aucune classe disponible.
            <div class="form-hint" style="margin-top:6px">
                Demandez au gestionnaire de vous assigner une classe.
            </div>
        </div>
    </div>
    @endif

@endsection
