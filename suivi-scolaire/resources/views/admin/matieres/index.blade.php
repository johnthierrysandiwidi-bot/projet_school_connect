@extends('layouts.app')

@section('title', 'Matières')
@section('page-title', 'Matières')
@section('page-subtitle', $annee)

@section('content')

    <div class="page-header">
        <form method="GET" action="{{ route('matieres.index') }}" class="filtre-group">
            <div class="form-group" style="margin-bottom:0">
                <label>Classe</label>
                <select name="classe_id" onchange="this.form.submit()">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('classe_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="page-actions">
            <a href="{{ route('matieres.create') }}" class="btn btn-primary">+ Nouvelle matière</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">📚 Matières ({{ $matieres->count() }})</div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Classe</th>
                        <th>Coefficient</th>
                        <th>Barème</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matieres as $matiere)
                    <tr>
                        <td><strong>{{ $matiere->nom }}</strong></td>
                        <td style="color:var(--color-text-muted)">{{ $matiere->code ?? '—' }}</td>
                        <td><span class="badge badge-blue">{{ $matiere->classe->nom ?? '-' }}</span></td>
                        <td>{{ $matiere->coefficient }}</td>
                        <td><span class="badge badge-gray">Sur {{ $matiere->bareme }}</span></td>
                        <td>
                            @if($matiere->is_active)
                                <span class="badge badge-green">✅ Active</span>
                            @else
                                <span class="badge badge-red">❌ Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('matieres.edit', $matiere) }}" class="btn btn-sm" style="background:#d97706; color:#fff;">✏️ Modifier</a>
                                <form action="{{ route('matieres.destroy', $matiere) }}" method="POST"
                                      data-confirm="Supprimer la matière « {{ $matiere->nom }} » ? Les notes déjà saisies pour cette matière seront aussi supprimées.">
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
                            Aucune matière{{ request('classe_id') ? ' pour cette classe' : '' }}.
                            <br><br>
                            <a href="{{ route('matieres.create') }}" class="btn btn-primary">+ Créer une matière</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
