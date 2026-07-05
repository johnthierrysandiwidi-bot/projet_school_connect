<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $paiement->reference }}</title>
    <style>
        @page { margin: 18px 22px; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12.5px; color: #1e293b; }

        .document {
            border: 1.4pt solid #1a56db;
            padding: 22px 26px;
        }

        /* En-tête : logo + identité école (gauche) / titre + référence (droite) */
        .header { display: table; width: 100%; margin-bottom: 14px; }
        .header-left { display: table-cell; width: 65%; vertical-align: top; }
        .header-right { display: table-cell; width: 35%; vertical-align: top; text-align: right; }

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
            max-width: 260px;
        }
        .ecole-coord { font-size: 10.5px; color: #64748b; margin-top: 6px; line-height: 1.5; }

        .doc-titre {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .doc-numero {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            border: 1pt solid #1a56db;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: #1a56db;
        }
        .doc-date { font-size: 10.5px; color: #64748b; margin-top: 6px; }

        .double-rule {
            border-top: 1.4pt solid #1a56db;
            border-bottom: 0.6pt solid #1a56db;
            height: 3px;
            margin-bottom: 18px;
        }

        /* Bloc infos : deux colonnes côte à côte */
        .info-cols { display: table; width: 100%; margin-bottom: 16px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 14px; }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #1a56db;
            background: #eff6ff;
            padding: 5px 8px;
            margin-bottom: 8px;
        }

        table.infos { width: 100%; border-collapse: collapse; }
        table.infos td { padding: 5px 2px; border-bottom: 0.6pt solid #e2e8f0; font-size: 12px; }
        table.infos td:first-child { color: #64748b; width: 42%; }
        table.infos td:last-child { font-weight: bold; }

        /* Montant */
        .montant-box {
            border: 1.4pt solid #0f172a;
            padding: 14px 16px;
            text-align: center;
            margin: 16px 0 6px;
        }
        .montant-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .montant-chiffres { font-size: 26px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .montant-lettres { font-size: 11px; font-style: italic; color: #475569; margin-top: 6px; }

        /* Récapitulatif */
        table.recap { width: 100%; border-collapse: collapse; margin-top: 4px; border: 0.6pt solid #cbd5e1; }
        table.recap td { padding: 7px 10px; font-size: 12px; border-bottom: 0.6pt solid #e2e8f0; }
        table.recap tr:last-child td { border-bottom: none; }
        table.recap td:first-child { color: #475569; }
        table.recap td:last-child { font-weight: bold; text-align: right; }

        .solde-ligne {
            margin-top: 0;
            padding: 9px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 12.5px;
            border: 1pt solid;
        }
        .solde-ok  { background: #f0fdf4; color: #065f46; border-color: #059669; }
        .solde-ko  { background: #fef2f2; color: #991b1b; border-color: #dc2626; }

        /* Signatures */
        .signature-box { display: table; width: 100%; margin: 26px 0 8px; }
        .signature { display: table-cell; text-align: center; width: 50%; vertical-align: top; }
        .cachet {
            border: 0.8pt dashed #94a3b8;
            border-radius: 4px;
            height: 54px;
            line-height: 54px;
            margin: 0 18px 8px;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }
        .signature-line {
            border-top: 0.8pt solid #0f172a;
            margin: 0 18px;
            padding-top: 5px;
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
        }

        .footer {
            border-top: 0.6pt solid #cbd5e1;
            margin-top: 14px;
            padding-top: 8px;
            text-align: center;
            font-size: 9.5px;
            color: #94a3b8;
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
                    Tél. {{ config('app.telephone_ecole') }}<br>
                @endif
                Année scolaire {{ config('app.annee_scolaire') }}
            </div>
        </div>
        <div class="header-right">
            <div class="doc-titre">REÇU DE PAIEMENT</div>
            <div class="doc-numero">N° {{ $paiement->reference }}</div>
            <div class="doc-date">Délivré le {{ $paiement->date_paiement->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="double-rule"></div>

    {{-- Informations élève / paiement, côte à côte --}}
    <div class="info-cols">
        <div class="info-col">
            <div class="section-title">Élève</div>
            <table class="infos">
                <tr><td>Nom complet</td><td>{{ $paiement->eleve->prenom }} {{ $paiement->eleve->nom }}</td></tr>
                <tr><td>Matricule</td><td>{{ $paiement->eleve->matricule }}</td></tr>
                <tr><td>Classe</td><td>{{ $paiement->eleve->classe->nom ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="info-col">
            <div class="section-title">Paiement</div>
            <table class="infos">
                <tr><td>Mode de paiement</td><td>{{ ucfirst($paiement->mode_paiement) }}</td></tr>
                <tr><td>Enregistré par</td><td>{{ $paiement->user->name }}</td></tr>
                <tr><td>Référence</td><td>{{ $paiement->reference }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Montant --}}
    <div class="montant-box">
        <div class="montant-label">Montant reçu</div>
        <div class="montant-chiffres">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</div>
        <div class="montant-lettres">
            {{ \App\Services\NombreEnLettresHelper::montantEnLettres($paiement->montant) }}
        </div>
    </div>

    {{-- Récapitulatif financier --}}
    @php
        $frais = $paiement->eleve->classe->frais_scolarite ?? 0;
        $totalPaye = $paiement->eleve->montant_paye;
        $reste = $paiement->eleve->reste_a_payer;
    @endphp
    <div class="section-title" style="margin-top:14px">Récapitulatif financier de l'élève</div>
    <table class="recap">
        <tr><td>Frais total de scolarité ({{ $paiement->eleve->classe->nom ?? '-' }})</td><td>{{ number_format($frais, 0, ',', ' ') }} FCFA</td></tr>
        <tr><td>Total payé à ce jour</td><td style="color:#059669">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</td></tr>
    </table>
    <div class="solde-ligne {{ $reste == 0 ? 'solde-ok' : 'solde-ko' }}" style="margin-top:8px">
        @if($reste == 0)
            Compte soldé — Aucun reste à payer
        @else
            Reste à payer : {{ number_format($reste, 0, ',', ' ') }} FCFA
        @endif
    </div>

    {{-- Signatures --}}
    <div class="signature-box">
        <div class="signature">
            <div class="cachet">Cachet de l'établissement</div>
            <div class="signature-line">Le Gestionnaire</div>
        </div>
        <div class="signature">
            <div class="cachet">&nbsp;</div>
            <div class="signature-line">Le Parent / Tuteur</div>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <strong>{{ config('app.nom_ecole') }}</strong> — Reçu généré électroniquement le {{ now()->format('d/m/Y à H:i') }} —
        Référence {{ $paiement->reference }} — Document à conserver
    </div>

</div>
</body>
</html>
