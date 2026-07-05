@extends('layouts.app')

@section('title', 'Passage à l\'année suivante')
@section('page-title', 'Passage à l\'année scolaire suivante')
@section('page-subtitle', "{$anneeActuelle} → {$anneeSuivante}")

@section('content')

    <div class="alert alert-info" style="margin-bottom:18px">
        <span>
            💡 Pour chaque élève, choisis s'il est <strong>promu</strong> en classe
            supérieure, <strong>redouble</strong> sa classe, ou <strong>quitte
            l'établissement</strong>. Les notes, paiements et absences de
            l'année {{ $anneeActuelle }} restent conservés tels quels — seule une
            nouvelle inscription est créée pour {{ $anneeSuivante }}.
            Une fois prêt, pense à activer {{ $anneeSuivante }} depuis la page
            <a href="{{ route('parametres.index') }}">Paramètres</a>.
        </span>
    </div>

    {{-- Sélecteur de classe --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('passage-annee.index') }}">
                <div class="filtre-group">
                    <div class="form-group" style="margin-bottom:0">
                        <label>Classe ({{ $anneeActuelle }})</label>
                        <select name="classe_id" onchange="this.form.submit()">
                            @forelse($classes as $c)
                            <option value="{{ $c->id }}" {{ ($classe && $classe->id == $c->id) ? 'selected' : '' }}>
                                {{ $c->nom }}
                            </option>
                            @empty
                            <option value="">Aucune classe pour {{ $anneeActuelle }}</option>
                            @endforelse
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($classe)
    <form action="{{ route('passage-annee.executer') }}" method="POST"
          data-confirm="Confirmer le passage d'année pour la classe {{ $classe->nom }} ? Cette action créera de nouvelles inscriptions pour {{ $anneeSuivante }}.">
    @csrf
    <input type="hidden" name="classe_id" value="{{ $classe->id }}">

        @if($niveauSuivant && $classesPromotion->count() > 1)
        <div class="alert alert-info" style="margin-bottom:18px">
            <span>
                ⚠️ Plusieurs classes de {{ $niveauSuivant }} existent déjà pour {{ $anneeSuivante }}.
                Choisis celle qui recevra les élèves promus :
                <select name="classe_destination_promotion" required style="margin-top:8px">
                    @foreach($classesPromotion as $cp)
                    <option value="{{ $cp->id }}">{{ $cp->nom }}</option>
                    @endforeach
                </select>
            </span>
        </div>
        @endif

        @if($classesRedoublement->count() > 1)
        <div class="alert alert-info" style="margin-bottom:18px">
            <span>
                ⚠️ Plusieurs classes de {{ $classe->niveau }} existent déjà pour {{ $anneeSuivante }}.
                Choisis celle qui recevra les élèves redoublants :
                <select name="classe_destination_redoublement" required style="margin-top:8px">
                    @foreach($classesRedoublement as $cr)
                    <option value="{{ $cr->id }}">{{ $cr->nom }}</option>
                    @endforeach
                </select>
            </span>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <span>
                    🎓 {{ $classe->nom }} — {{ $eleves->count() }} élève(s) actif(s)
                    @if($niveauSuivant)
                        — promotion vers <strong>{{ $niveauSuivant }}</strong>
                    @else
                        — dernière classe du cycle primaire
                    @endif
                </span>
                @if($eleves->isNotEmpty())
                <div class="cell-actions">
                    @if($niveauSuivant)
                        <button type="button" class="btn btn-outline btn-sm" onclick="appliquerATous('promouvoir')">Tout promouvoir</button>
                    @endif
                    <button type="button" class="btn btn-outline btn-sm" onclick="appliquerATous('redoubler')">Tout faire redoubler</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="appliquerATous('quitter')">{{ $niveauSuivant ? 'Tout faire quitter' : 'Tout marquer diplômé' }}</button>
                </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Matricule</th>
                            <th>Décision pour {{ $anneeSuivante }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eleves as $eleve)
                        <tr>
                            <td>{{ $eleve->prenom }} {{ $eleve->nom }}</td>
                            <td style="font-family:monospace; color:var(--color-text-muted); font-size:12px">{{ $eleve->matricule }}</td>
                            <td>
                                @if($eleve->eleveSuivant)
                                    <span class="badge badge-green">
                                        ✅ Déjà traité → {{ $eleve->eleveSuivant->classe->nom ?? '?' }} ({{ $eleve->eleveSuivant->annee_scolaire }})
                                    </span>
                                @else
                                    <select name="decisions[{{ $eleve->id }}]" class="decision-select">
                                        @if($niveauSuivant)
                                            <option value="promouvoir" selected>➡️ Promouvoir en {{ $niveauSuivant }}</option>
                                            <option value="redoubler">🔁 Redoubler {{ $classe->nom }}</option>
                                            <option value="quitter">🚪 Quitter l'établissement</option>
                                        @else
                                            <option value="quitter" selected>🎓 Diplômé — fin du cycle primaire</option>
                                            <option value="redoubler">🔁 Redoubler {{ $classe->nom }}</option>
                                        @endif
                                    </select>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="3">Aucun élève actif dans cette classe.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($eleves->isNotEmpty())
            <div style="padding:16px 22px; border-top:1px solid var(--color-border); text-align:right">
                <button type="submit" class="btn btn-primary">✅ Valider le passage pour {{ $classe->nom }}</button>
            </div>
            @endif
        </div>
    </form>
    @else
    <div class="card">
        <div class="empty-state">
            <div class="icon">📭</div>
            Aucune classe disponible pour l'année {{ $anneeActuelle }}.
        </div>
    </div>
    @endif

@endsection

@push('scripts')
<script>
function appliquerATous(valeur) {
    document.querySelectorAll('.decision-select').forEach(select => {
        select.value = valeur;
    });
}
</script>
@endpush
