<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Eleve extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'eleve_origine_id', 'matricule', 'nom', 'prenom', 'date_naissance',
        'lieu_naissance', 'sexe', 'nationalite', 'photo',
        'classe_id', 'annee_scolaire', 'statut',
        'parent_nom', 'parent_prenom', 'parent_telephone',
        'parent_telephone2', 'parent_adresse', 'parent_lien',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // Le dossier de cet élève pour l'année scolaire précédente (avant un
    // passage en classe supérieure ou un redoublement), s'il existe.
    public function eleveOrigine()
    {
        return $this->belongsTo(Eleve::class, 'eleve_origine_id');
    }

    // Le dossier de cet élève pour l'année scolaire suivante, une fois le
    // passage effectué (null si pas encore traité).
    public function eleveSuivant()
    {
        return $this->hasOne(Eleve::class, 'eleve_origine_id');
    }

    // Un élève appartient à une classe
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Un élève a plusieurs paiements
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    // Un élève a plusieurs notes
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    // Un élève a plusieurs absences
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // Les comptes parents liés à cet élève
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_eleve');
    }

    // Nom complet de l'élève
    public function getNomCompletAttribute()
    {
        return $this->nom . ' ' . $this->prenom;
    }

    // URL publique de la photo (utilisée par l'API mobile), ou null.
    // On utilise asset() plutôt que Storage::disk('public')->url() : asset()
    // se base sur l'URL réellement utilisée pour accéder au site, alors que
    // Storage::url() se base sur APP_URL dans .env — qui ne correspond pas
    // toujours à l'adresse réelle (ex. Laragon avec un nom de domaine local
    // du type http://suivi-scolaire.test, alors que APP_URL vaut
    // http://localhost). Avec asset(), les photos s'affichent correctement
    // quel que soit l'environnement, sans rien à configurer.
    //
    // On vérifie aussi que le fichier existe réellement sur le disque : si
    // la colonne `photo` contient un chemin mais que le fichier a disparu
    // (nettoyage manuel, restauration partielle de la base...), on renvoie
    // null plutôt qu'une URL cassée — l'interface affiche alors les
    // initiales de l'élève au lieu de l'icône "image cassée" du navigateur.
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo)) {
            return null;
        }

        return asset('storage/' . $this->photo);
    }

    // Photo encodée en base64 (data URI), pour l'intégrer directement dans
    // un PDF généré par DomPDF. DomPDF ne charge pas les images distantes
    // par défaut (isRemoteEnabled = false) : on lit donc le fichier
    // directement sur le disque plutôt que de passer par une URL HTTP.
    public function getPhotoBase64Attribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        $chemin = \Illuminate\Support\Facades\Storage::disk('public')->path($this->photo);

        if (! file_exists($chemin)) {
            return null;
        }

        $type = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($this->photo) ?: 'image/jpeg';

        return 'data:' . $type . ';base64,' . base64_encode(file_get_contents($chemin));
    }

    // Total payé par l'élève
    public function getMontantPayeAttribute()
    {
        return $this->paiements()->sum('montant');
    }

    // Ce qu'il reste à payer
    public function getResteAPayerAttribute()
    {
        $frais = $this->classe ? $this->classe->frais_scolarite : 0;
        return max(0, $frais - $this->montant_paye);
    }

    // Générer automatiquement le matricule
    public static function genererMatricule()
    {
        $annee = date('Y');
        $count = self::withTrashed()->whereYear('created_at', $annee)->count() + 1;
        return 'EL-' . $annee . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}