@extends('layouts.app')

@section('title', 'Classement')
@section('page-title', 'Classement des élèves')
@section('page-subtitle', "Trimestre {$trimestre} — {$annee}")

@section('content')

    {{-- Filtres --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('notes.classement') }}">
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
                    <a href="{{ route('notes.index', ['classe_id' => $classe?->id, 'trimestre' => $trimestre]) }}" class="btn btn-outline">
                        📝 Saisir des notes
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($classe)
    <div class="card">
        <div class="card-header">
            🏆 Classement — {{ $classe->nom }} — Trimestre {{ $trimestre }} — {{ $annee }}
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Élève</th>
                        <th>Sexe</th>
                        <th>Moyenne</th>
                        <th>Mention</th>
                        <th>Statut</th>
                        <th>Bulletin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classement as $eleve)
                    <tr>
                        <td>
                            @if($eleve->rang == 1)
                                <span class="rank-medal">🥇</span>
                            @elseif($eleve->rang == 2)
                                <span class="rank-medal">🥈</span>
                            @elseif($eleve->rang == 3)
                                <span class="rank-medal">🥉</span>
                            @else
                                <strong>{{ $eleve->rang }}</strong>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $eleve->prenom }} {{ $eleve->nom }}</strong>
                            <div class="form-hint">{{ $eleve->matricule }}</div>
                        </td>
                        <td>{{ $eleve->sexe === 'M' ? '♂' : '♀' }}</td>
                        <td>
                            @if($eleve->moyenne !== null)
                                <strong style="font-size:15px; color:{{ $eleve->moyenne >= 5 ? '#059669' : '#b91c1c' }}">
                                    {{ $eleve->moyenne }}/10
                                </strong>
                            @else
                                <span style="color:#94a3b8">—</span>
                            @endif
                        </td>
                        <td>
                            @if($eleve->moyenne !== null)
                                @php
                                    $mention = match(true) {
                                        $eleve->moyenne >= 9 => 'Excellent',
                                        $eleve->moyenne >= 8 => 'Très Bien',
                                        $eleve->moyenne >= 7 => 'Bien',
                                        $eleve->moyenne >= 6 => 'Assez Bien',
                                        $eleve->moyenne >= 5 => 'Passable',
                                        default => 'Insuffisant',
                                    };
                                @endphp
                                {{ $mention }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($eleve->moyenne === null)
                                <span class="badge badge-gray">Pas de notes</span>
                            @elseif($eleve->moyenne >= 5)
                                <span class="badge badge-green">✅ Admis</span>
                            @else
                                <span class="badge badge-red">❌ Insuffisant</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('notes.bulletin', ['eleve' => $eleve->id, 'trimestre' => $trimestre]) }}"
                               class="btn btn-primary btn-sm">
                                📄 PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            Aucune note saisie pour ce trimestre.
                            <br><br>
                            <a href="{{ route('notes.index', ['classe_id' => $classe->id, 'trimestre' => $trimestre]) }}" class="btn btn-primary">
                                → Saisir les notes
                            </a>
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
        </div>
    </div>
    @endif

@endsection
