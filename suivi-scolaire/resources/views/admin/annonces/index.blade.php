@extends('layouts.app')

@section('title', 'Annonces')
@section('page-title', 'Annonces & notifications')
@section('page-subtitle', 'Visibles par les parents dans leur application mobile')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('annonces.create') }}" class="btn btn-primary">+ Publier une annonce</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">📢 Annonces publiées ({{ $annonces->total() }})</div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Destinataire</th>
                        <th>Date</th>
                        <th>Publiée par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($annonces as $annonce)
                    <tr>
                        <td>
                            <strong>{{ $annonce->icone }} {{ $annonce->titre }}</strong>
                            <div class="form-hint">{{ \Illuminate\Support\Str::limit($annonce->contenu, 70) }}</div>
                        </td>
                        <td><span class="badge badge-blue">{{ ucfirst($annonce->type) }}</span></td>
                        <td>
                            @if($annonce->classe)
                                <span class="badge badge-gray">{{ $annonce->classe->nom }}</span>
                            @else
                                <span class="badge badge-green">Toute l'école</span>
                            @endif
                        </td>
                        <td>{{ $annonce->date_publication->format('d/m/Y') }}</td>
                        <td>{{ $annonce->user->name }}</td>
                        <td>
                            <form action="{{ route('annonces.destroy', $annonce) }}" method="POST"
                                  data-confirm="Retirer l'annonce « {{ $annonce->titre }} » ?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6">
                            Aucune annonce publiée.
                            <br><br>
                            <a href="{{ route('annonces.create') }}" class="btn btn-primary">+ Publier une annonce</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($annonces->hasPages())
        <div style="padding:6px 22px 18px;">{{ $annonces->links() }}</div>
        @endif
    </div>

@endsection
