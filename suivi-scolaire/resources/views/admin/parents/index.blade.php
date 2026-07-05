@extends('layouts.app')

@section('title', 'Comptes parents')
@section('page-title', 'Comptes parents')
@section('page-subtitle', "Accès à l'application mobile de suivi")

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('parents.create') }}" class="btn btn-primary">+ Nouveau compte parent</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">👨‍👩‍👧 Comptes parents ({{ $parents->count() }})</div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Enfant(s)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                    <tr>
                        <td><div class="avatar">{{ strtoupper(substr($parent->name, 0, 2)) }}</div></td>
                        <td><strong>{{ $parent->name }}</strong></td>
                        <td style="color:var(--color-text-muted)">{{ $parent->email }}</td>
                        <td>
                            @forelse($parent->enfants as $enfant)
                                <span class="badge badge-blue">{{ $enfant->nom }} {{ $enfant->prenom }} ({{ $enfant->classe->nom ?? '-' }})</span>
                            @empty
                                <span class="badge badge-red">Aucun enfant lié</span>
                            @endforelse
                        </td>
                        <td>
                            @if($parent->is_active)
                                <span class="badge badge-green">✅ Actif</span>
                            @else
                                <span class="badge badge-red">❌ Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('parents.edit', $parent) }}" class="btn btn-sm" style="background:#d97706; color:#fff;">✏️ Modifier</a>
                                <form action="{{ route('parents.destroy', $parent) }}" method="POST"
                                      data-confirm="Supprimer le compte parent de {{ $parent->name }} ?">
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
                            Aucun compte parent créé.
                            <br><br>
                            <a href="{{ route('parents.create') }}" class="btn btn-primary">+ Créer le premier compte</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
