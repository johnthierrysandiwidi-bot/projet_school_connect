package bf.ecole.suiviscolaire.data.model

import com.google.gson.annotations.SerializedName

// --- Authentification ---

data class LoginRequest(
    val email: String,
    val password: String
)

data class LoginResponse(
    val token: String,
    @SerializedName("parent") val parentInfo: ParentInfo,
    val enfants: List<Eleve>
)

data class ParentInfo(
    val id: Int,
    val nom: String,
    val email: String
)

data class ChangePasswordRequest(
    @SerializedName("current_password") val currentPassword: String,
    val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String
)

data class MessageResponse(
    val message: String
)

// --- Élève ---

data class Eleve(
    val id: Int,
    val matricule: String,
    val nom: String,
    val prenom: String,
    @SerializedName("nom_complet") val nomComplet: String,
    val sexe: String,
    @SerializedName("date_naissance") val dateNaissance: String?,
    @SerializedName("photo_url") val photoUrl: String?,
    val classe: Classe?
)

// Une classe peut désormais avoir plusieurs sections pour un même niveau
// (ex. CP1 A, CP1 B) : `nom` est le libellé précis à afficher, `niveau`
// reste utile pour le regroupement pédagogique (CP1...CM2).
data class Classe(
    val id: Int,
    val niveau: String,
    val nom: String
)

// Réponse de GET /api/enfants : Laravel enveloppe automatiquement une
// collection de ressources renvoyée directement dans une clé "data".
data class EnfantsListResponse(
    val data: List<Eleve>
)

// --- Tableau de bord d'un enfant ---

data class DashboardResponse(
    val eleve: Eleve,
    val trimestre: Int,
    @SerializedName("annee_scolaire") val anneeScolaire: String,
    @SerializedName("moyenne_generale") val moyenneGenerale: Double?,
    val rang: Int?,
    @SerializedName("total_eleves") val totalEleves: Int,
    @SerializedName("dernieres_notes") val dernieresNotes: List<DerniereNote>
)

data class DerniereNote(
    val matiere: String,
    val valeur: Double,
    val bareme: Int,
    val trimestre: Int,
    val date: String
)

// --- Notes par matière et par trimestre ---

data class NotesResponse(
    val eleve: String,
    @SerializedName("annee_scolaire") val anneeScolaire: String,
    val trimestres: List<TrimestreNotes>
)

data class TrimestreNotes(
    val trimestre: Int,
    val moyenne: Double?,
    val matieres: List<MatiereNote>
)

data class MatiereNote(
    val matiere: String,
    val coefficient: Double,
    val bareme: Int,
    val valeur: Double?
)

// --- Paiements ---

data class PaiementsResponse(
    @SerializedName("frais_total") val fraisTotal: Double,
    @SerializedName("montant_paye") val montantPaye: Double,
    @SerializedName("reste_a_payer") val resteAPayer: Double,
    val paiements: List<Paiement>
)

data class Paiement(
    val id: Int,
    val reference: String,
    val montant: Double,
    @SerializedName("date_paiement") val datePaiement: String,
    @SerializedName("mode_paiement") val modePaiement: String
)

// --- Absences ---

data class AbsencesResponse(
    val total: Int,
    val absences: List<Absence>
)

data class Absence(
    val id: Int,
    @SerializedName("date_absence") val dateAbsence: String,
    val justifiee: Boolean,
    val motif: String?
)

// --- Annonces ---

data class AnnoncesResponse(
    @SerializedName("non_lues") val nonLues: Int,
    val annonces: List<Annonce>
)

data class Annonce(
    val id: Int,
    val titre: String,
    val contenu: String,
    val type: String,
    val icone: String,
    @SerializedName("date_publication") val datePublication: String,
    val classe: String?,
    val lu: Boolean
)
