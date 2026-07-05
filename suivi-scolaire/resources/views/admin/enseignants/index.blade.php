@extends('layouts.app')

@section('title', 'Enseignants')
@section('page-title', 'Gestion des enseignants')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('enseignants.create') }}" class="btn btn-primary">+ Nouvel enseignant</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>🧑‍🏫 Liste des enseignants ({{ $enseignants->count() }})</span>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Classe assignée</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enseignants as $enseignant)
                    <tr>
                        <td><div class="avatar">{{ strtoupper(substr($enseignant->name, 0, 2)) }}</div></td>
                        <td><strong>{{ $enseignant->name }}</strong></td>
                        <td style="color:var(--color-text-muted)">{{ $enseignant->email }}</td>
                        <td>
                            @if($enseignant->classe)
                                <span class="badge badge-blue">{{ $enseignant->classe->nom }}</span>
                            @else
                                <span class="badge badge-red">Non assigné</span>
                            @endif
                        </td>
                        <td>
                            @if($enseignant->is_active)
                                <span class="badge badge-green">✅ Actif</span>
                            @else
                                <span class="badge badge-red">❌ Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('enseignants.edit', $enseignant) }}" class="btn btn-sm" style="background:#d97706; color:#fff;">✏️ Modifier</a>
                                <form action="{{ route('enseignants.destroy', $enseignant) }}" method="POST"
                                      data-confirm="Supprimer l'enseignant {{ $enseignant->name }} ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6">
                            Aucun enseignant créé.
                            <br><br>
                            <a href="{{ route('enseignants.create') }}" class="btn btn-primary">+ Créer le premier enseignant</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
