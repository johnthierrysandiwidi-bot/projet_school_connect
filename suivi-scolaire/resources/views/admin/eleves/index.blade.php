@extends('layouts.app')

@section('title', 'Élèves')
@section('page-title', 'Gestion des élèves')

@section('content')

    <div class="page-header">
        <div></div>
        <div class="page-actions">
            <a href="{{ route('eleves.create') }}" class="btn btn-primary">+ Inscrire un élève</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>👨‍🎓 Liste des élèves ({{ $eleves->total() }})</span>
        </div>

        {{-- Barre de recherche --}}
        <form method="GET" action="{{ route('eleves.index') }}" style="padding:16px 22px; background:#f8fafc; border-bottom:1px solid var(--color-border);">
            <div class="search-group">
                <div class="form-group" style="margin-bottom:0">
                    <label>🔍 Rechercher</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nom, prénom ou matricule…" style="min-width:220px">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Classe</label>
                    <select name="classe_id">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $classe)
                        <option value="{{ $classe->id }}" {{ request('classe_id') == $classe->id ? 'selected' : '' }}>
                            {{ $classe->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label>Sexe</label>
                    <select name="sexe">
                        <option value="">Tous</option>
                        <option value="M" {{ request('sexe') == 'M' ? 'selected' : '' }}>Garçons</option>
                        <option value="F" {{ request('sexe') == 'F' ? 'selected' : '' }}>Filles</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary">🔍 Chercher</button>
                    <a href="{{ route('eleves.index') }}" class="btn btn-light">✕ Effacer</a>
                </div>
            </div>
        </form>

        @if(request('search') || request('classe_id') || request('sexe'))
        <div class="alert alert-info" style="margin:16px 22px 0;">
            <span>
                🔍 {{ $eleves->total() }} résultat(s) trouvé(s)
                @if(request('search')) pour "<strong>{{ request('search') }}</strong>" @endif
                @if(request('classe_id'))
                    dans la classe <strong>{{ $classes->firstWhere('id', request('classe_id'))->nom ?? '' }}</strong>
                @endif
            </span>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Matricule</th>
                        <th>Prénom &amp; Nom</th>
                        <th>Classe</th>
                        <th>Sexe</th>
                        <th>Parent</th>
                        <th>Téléphone</th>
                        <th>Situation financière</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eleves as $eleve)
                    @php
                        $frais = $eleve->classe ? $eleve->classe->frais_scolarite : 0;
                        $paye = $eleve->montant_paye;
                        $reste = $eleve->reste_a_payer;
                        $pct = $frais > 0 ? min(100, round($paye / $frais * 100)) : 0;
                    @endphp
                    <tr>
                        <td>
                            @if($eleve->photo_url)
                                <img src="{{ $eleve->photo_url }}" class="avatar" alt="">
                            @else
                                <div class="avatar">{{ strtoupper(substr($eleve->prenom, 0, 1) . substr($eleve->nom, 0, 1)) }}</div>
                            @endif
                        </td>
                        <td style="font-family:monospace; color:var(--color-text-muted); font-size:12px">
                            {{ $eleve->matricule }}
                        </td>
                        <td>
                            <strong>{{ $eleve->nom }} {{ $eleve->prenom }}</strong>
                            <div class="form-hint">{{ $eleve->date_naissance->format('d/m/Y') }}</div>
                        </td>
                        <td><span class="badge badge-blue">{{ $eleve->classe->nom ?? '-' }}</span></td>
                        <td>
                            <span class="badge {{ $eleve->sexe === 'M' ? 'badge-blue' : 'badge-red' }}">
                                {{ $eleve->sexe === 'M' ? '♂ Garçon' : '♀ Fille' }}
                            </span>
                        </td>
                        <td>{{ $eleve->parent_nom }} {{ $eleve->parent_prenom }}</td>
                        <td>{{ $eleve->parent_telephone }}</td>
                        <td style="min-width:130px">
                            <div style="font-size:12px; color:{{ $reste > 0 ? '#b91c1c' : '#059669' }}; font-weight:bold">
                                {{ $reste > 0 ? number_format($reste, 0, ',', ' ').' F restants' : '✓ Soldé' }}
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill"
                                     style="width:{{ $pct }}%;
                                     background:{{ $pct == 100 ? '#059669' : ($pct >= 50 ? '#d97706' : '#ef4444') }}">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a href="{{ route('eleves.show', $eleve) }}" class="btn btn-outline btn-sm">👁 Voir</a>
                                <a href="{{ route('eleves.edit', $eleve) }}" class="btn btn-sm" style="background:#d97706; color:#fff;">✏️</a>
                                <form action="{{ route('eleves.destroy', $eleve) }}" method="POST"
                                      data-confirm="Supprimer cet élève ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="9">
                            @if(request('search') || request('classe_id') || request('sexe'))
                                🔍 Aucun élève trouvé pour cette recherche.
                                <br><br>
                                <a href="{{ route('eleves.index') }}" class="btn btn-primary">Voir tous les élèves</a>
                            @else
                                Aucun élève inscrit.
                                <br><br>
                                <a href="{{ route('eleves.create') }}" class="btn btn-primary">+ Inscrire le premier élève</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($eleves->hasPages())
        <div style="padding:6px 22px 18px;">
            {{ $eleves->links() }}
        </div>
        @endif
    </div>

@endsection
