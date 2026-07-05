<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin — {{ $eleve->prenom }} {{ $eleve->nom }}</title>
    <style>
        @page { margin: 18px 22px; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12.5px; color: #1e293b; }

        .document {
            border: 1.4pt solid #1a56db;
            padding: 20px 24px;
        }

        /* En-tête : identité école (gauche) / titre + photo (droite) */
        .header { display: table; width: 100%; margin-bottom: 12px; }
        .header-left { display: table-cell; width: 68%; vertical-align: top; }
        .header-right { display: table-cell; width: 32%; vertical-align: top; text-align: right; }

        .logo-badge {
            display: inline-block;
            width: 38px; height: 38px;
            background: #1a56db;
            color: #fff;
            border-radius: 8px;
            text-align: center;
            line-height: 38px;
            font-size: 20px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            vertical-align: middle;
        }
        .ecole-nom {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #1a56db;
        }
        .ecole-coord { font-size: 10.5px; color: #64748b; margin-top: 6px; line-height: 1.5; }

        .doc-titre { font-size: 15px; font-weight: bold; letter-spacing: 0.5px; color: #0f172a; }
        .doc-sous-titre { font-size: 11px; color: #64748b; margin-top: 4px; }

        .photo-eleve {
            width: 56px; height: 56px;
            border: 1pt solid #cbd5e1;
            border-radius: 6px;
            margin-top: 8px;
            margin-left: auto;
        }
        .photo-placeholder {
            width: 56px; height: 56px;
            border: 1pt solid #cbd5e1;
            border-radius: 6px;
            margin-top: 8px;
            margin-left: auto;
            background: #eff6ff;
            color: #1a56db;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 56px;
        }

        .double-rule {
            border-top: 1.4pt solid #1a56db;
            border-bottom: 0.6pt solid #1a56db;
            height: 3px;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #1a56db;
            background: #eff6ff;
            padding: 5px 8px;
            margin-bottom: 8px;
            margin-top: 14px;
        }

        .info-grid { display: table; width: 100%; border: 0.6pt solid #cbd5e1; margin-bottom: 4px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 6px 10px; border-bottom: 0.6pt solid #e2e8f0; font-size: 11.5px; }
        .info-cell:first-child { color: #64748b; width: 28%; background: #f8fafc; }
        .info-cell:last-child { font-weight: bold; }
        .info-row:last-child .info-cell { border-bottom: none; }

        table.notes { width: 100%; border-collapse: collapse; margin-bottom: 4px; border: 0.6pt solid #cbd5e1; }
        table.notes th {
            background: #1a56db; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        table.notes td { padding: 7px 8px; border-bottom: 0.6pt solid #e2e8f0; font-size: 12px; }
        table.notes tr:nth-child(even) td { background: #f8fafc; }
        table.notes tfoot td { border-top: 1pt solid #1a56db; border-bottom: none; font-weight: bold; background: #eff6ff; }

        .mention {
            display: inline-block;
            padding: 2px 9px;
            border: 0.6pt solid;
            border-radius: 3px;
            font-size: 10.5px;
            font-weight: bold;
        }
        .mention-excellent { color: #065f46; border-color: #059669; }
        .mention-bien { color: #1d4ed8; border-color: #1a56db; }
        .mention-passable { color: #92400e; border-color: #d97706; }
        .mention-insuffisant { color: #991b1b; border-color: #dc2626; }

        /* Synthèse : moyenne + rang côte à côte */
        .synthese { display: table; width: 100%; margin: 14px 0; border-collapse: collapse; }
        .synthese-cell {
            display: table-cell; width: 50%; text-align: center; vertical-align: middle;
            border: 1.4pt solid #0f172a; padding: 12px;
        }
        .synthese-cell + .synthese-cell { border-left: none; }
        .synthese-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px; color: #64748b; }
        .synthese-value { font-size: 24px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .synthese-sub { font-size: 11px; font-weight: bold; margin-top: 4px; }

        .appreciation {
            border: 0.6pt solid #cbd5e1;
            background: #f8fafc;
            padding: 9px 12px;
            font-size: 11.5px;
            font-style: italic;
            color: #334155;
            margin-bottom: 4px;
        }
        .appreciation strong { font-style: normal; color: #0f172a; }

        .signature-grid { display: table; width: 100%; margin: 22px 0 8px; }
        .signature-cell { display: table-cell; width: 33.33%; text-align: center; padding: 0 8px; vertical-align: top; }
        .cachet-mini {
            border: 0.8pt dashed #94a3b8; border-radius: 4px; height: 44px; line-height: 44px;
            color: #94a3b8; font-size: 9px; text-align: center;
        }
        .signature-line {
            border-top: 0.8pt solid #0f172a; margin-top: 6px; padding-top: 5px;
            font-size: 10.5px; font-weight: bold; color: #1e293b;
        }

        .footer {
            border-top: 0.6pt solid #cbd5e1; margin-top: 12px; padding-top: 8px;
            text-align: center; font-size: 9.5px; color: #94a3b8;
        }
        .footer strong { color: #64748b; }
    </style>
</head>
<body>
<div class="document">

    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <span class="logo-badge">{{ mb_substr(config('app.nom_ecole'), 0, 1) }}</span>
            <span class="ecole-nom">{{ config('app.nom_ecole') }}</span>
            <div class="ecole-coord">
                @if(config('app.adresse_ecole'))
                    {{ config('app.adresse_ecole') }}<br>
                @endif
                @if(config('app.telephone_ecole'))
                    Tél. {{ config('app.telephone_ecole') }}
                @endif
            </div>
        </div>
        <div class="header-right">
            <div class="doc-titre">BULLETIN DE NOTES</div>
            <div class="doc-sous-titre">Trimestre {{ $trimestre }} — Année scolaire {{ $annee }}</div>
            @if($eleve->photo_base64)
                <img src="{{ $eleve->photo_base64 }}" class="photo-eleve" alt="">
            @else
                <div class="photo-placeholder">{{ mb_substr($eleve->prenom, 0, 1) }}{{ mb_substr($eleve->nom, 0, 1) }}</div>
            @endif
        </div>
    </div>

    <div class="double-rule"></div>

    {{-- Informations élève --}}
    <div class="section-title" style="margin-top:0">Informations de l'élève</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">Prénom &amp; Nom</div>
            <div class="info-cell">{{ $eleve->prenom }} {{ $eleve->nom }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell">Matricule</div>
            <div class="info-cell">{{ $eleve->matricule }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell">Classe</div>
            <div class="info-cell">{{ $eleve->classe->nom }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell">Date de naissance</div>
            <div class="info-cell">{{ $eleve->date_naissance->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell">Sexe</div>
            <div class="info-cell">{{ $eleve->sexe === 'M' ? 'Masculin' : 'Féminin' }}</div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="section-title">Notes du Trimestre {{ $trimestre }}</div>
    @php
        $totalCoeff = 0;
        $totalPoints = 0;
    @endphp
    <table class="notes">
        <thead>
            <tr>
                <th>Matière</th>
                <th style="text-align:center">Coefficient</th>
                <th style="text-align:center">Note</th>
                <th style="text-align:center">Points</th>
                <th>Mention</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matieres as $matiere)
            @php
                $note = $notes[$matiere->id] ?? null;
                $valeur = $note ? $note->valeur : null;
                // Note ramenée sur 10 avant pondération, exactement comme dans
                // MoyenneService::moyenneEleve() — pour que le total de cette
                // colonne, divisé par le total des coefficients, redonne
                // exactement la moyenne générale affichée plus bas.
                $points = $valeur !== null ? ($valeur / $matiere->bareme) * 10 * $matiere->coefficient : null;
                // Mentions calculées en pourcentage du barème de CETTE matière
                // (10 ou 20), pour rester correctes quelle que soit l'échelle.
                $pourcentage = $valeur !== null ? ($valeur / $matiere->bareme) * 100 : null;
                $mention = null;
                if ($pourcentage !== null) {
                    $mention = match(true) {
                        $pourcentage >= 90 => ['label' => 'Excellent', 'class' => 'mention-excellent'],
                        $pourcentage >= 80 => ['label' => 'Très Bien', 'class' => 'mention-bien'],
                        $pourcentage >= 70 => ['label' => 'Bien', 'class' => 'mention-bien'],
                        $pourcentage >= 60 => ['label' => 'Assez Bien', 'class' => 'mention-bien'],
                        $pourcentage >= 50 => ['label' => 'Passable', 'class' => 'mention-passable'],
                        default => ['label' => 'Insuffisant', 'class' => 'mention-insuffisant'],
                    };
                    $totalCoeff += $matiere->coefficient;
                    $totalPoints += $points;
                }
            @endphp
            <tr>
                <td><strong>{{ $matiere->nom }}</strong></td>
                <td style="text-align:center">{{ $matiere->coefficient }}</td>
                <td style="text-align:center; font-weight:bold;
                    color:{{ $valeur !== null ? ($pourcentage >= 50 ? '#059669' : '#b91c1c') : '#94a3b8' }}">
                    {{ $valeur !== null ? number_format($valeur, 2) . '/' . $matiere->bareme : '—' }}
                </td>
                <td style="text-align:center">
                    {{ $points !== null ? number_format($points, 2) : '—' }}
                </td>
                <td>
                    @if($mention)
                        <span class="mention {{ $mention['class'] }}">{{ $mention['label'] }}</span>
                    @else
                        <span style="color:#94a3b8">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td style="text-align:center">{{ $totalCoeff > 0 ? number_format($totalCoeff, 1) : '—' }}</td>
                <td></td>
                <td style="text-align:center">{{ $totalCoeff > 0 ? number_format($totalPoints, 2) : '—' }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div style="font-size:9.5px; color:#94a3b8; font-style:italic; margin-bottom:10px;">
        Points = note ramenée sur 10 × coefficient (base commune permettant de comparer des matières
        notées sur des barèmes différents). Moyenne générale = total des points ÷ total des coefficients.
    </div>

    {{-- Synthèse : moyenne + rang --}}
    <div class="synthese">
        <div class="synthese-cell">
            <div class="synthese-label">Moyenne générale du trimestre</div>
            <div class="synthese-value">
                @if($moyenne !== null)
                    {{ number_format($moyenne, 2) }} / 10
                @else
                    — / 10
                @endif
            </div>
            @if($moyenne !== null)
            <div class="synthese-sub" style="color:{{ $moyenne >= 5 ? '#059669' : '#b91c1c' }}">
                {{ $moyenne >= 9 ? 'Excellent' : ($moyenne >= 8 ? 'Très Bien' : ($moyenne >= 7 ? 'Bien' : ($moyenne >= 6 ? 'Assez Bien' : ($moyenne >= 5 ? 'Passable' : 'Insuffisant')))) }}
            </div>
            @endif
        </div>
        <div class="synthese-cell">
            <div class="synthese-label">Rang dans la classe</div>
            <div class="synthese-value">
                @if($rang !== null)
                    {{ $rang }}<sup>{{ $rang === 1 ? 'er' : 'e' }}</sup> / {{ $totalEleves }}
                @else
                    —
                @endif
            </div>
            <div class="synthese-sub" style="color:#64748b">
                @if($rang === null) Pas encore classé @endif
            </div>
        </div>
    </div>

    {{-- Appréciation générale (générée automatiquement à partir de la moyenne) --}}
    @php
        $appreciation = match(true) {
            $moyenne === null => null,
            $moyenne >= 9 => "Excellent trimestre. Continuez sur cette voie, c'est remarquable.",
            $moyenne >= 8 => "Très bon trimestre. Des résultats solides, à poursuivre.",
            $moyenne >= 7 => "Bon trimestre. Encore un peu plus de régularité et les résultats seront excellents.",
            $moyenne >= 6 => "Trimestre assez satisfaisant. Des efforts supplémentaires permettront de progresser.",
            $moyenne >= 5 => "Résultats passables. Un travail plus soutenu est nécessaire pour la suite.",
            default => "Trimestre difficile. Un accompagnement renforcé est recommandé.",
        };
    @endphp
    @if($appreciation)
    <div class="appreciation"><strong>Appréciation générale :</strong> {{ $appreciation }}</div>
    @endif

    {{-- Signatures --}}
    <div class="signature-grid">
        <div class="signature-cell">
            <div class="cachet-mini">Cachet de l'école</div>
            <div class="signature-line">Le Directeur</div>
        </div>
        <div class="signature-cell">
            <div class="cachet-mini">&nbsp;</div>
            <div class="signature-line">L'Enseignant</div>
        </div>
        <div class="signature-cell">
            <div class="cachet-mini">&nbsp;</div>
            <div class="signature-line">Le Parent / Tuteur</div>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <strong>{{ config('app.nom_ecole') }}</strong> — Bulletin généré électroniquement le {{ now()->format('d/m/Y à H:i') }} —
        Matricule {{ $eleve->matricule }}
    </div>

</div>
</body>
</html>
