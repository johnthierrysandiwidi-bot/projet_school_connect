package bf.ecole.suiviscolaire.data

import android.content.Context
import android.content.SharedPreferences

/**
 * Stocke le jeton d'authentification et l'enfant actuellement sélectionné
 * (un parent peut avoir plusieurs enfants).
 *
 * Pour un usage en production, remplacer SharedPreferences par
 * EncryptedSharedPreferences (androidx.security.crypto) afin de chiffrer le
 * jeton sur le disque.
 */
class SessionManager(context: Context) {

    private val prefs: SharedPreferences =
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        set(value) = prefs.edit().putString(KEY_TOKEN, value).apply()

    var selectedEleveId: Int
        get() = prefs.getInt(KEY_ELEVE_ID, -1)
        set(value) = prefs.edit().putInt(KEY_ELEVE_ID, value).apply()

    var parentName: String?
        get() = prefs.getString(KEY_PARENT_NAME, null)
        set(value) = prefs.edit().putString(KEY_PARENT_NAME, value).apply()

    fun isLoggedIn(): Boolean = !token.isNullOrEmpty()

    fun clear() {
        prefs.edit().clear().apply()
    }

    companion object {
        private const val PREFS_NAME = "suivi_scolaire_prefs"
        private const val KEY_TOKEN = "auth_token"
        private const val KEY_ELEVE_ID = "selected_eleve_id"
        private const val KEY_PARENT_NAME = "parent_name"
    }
}
