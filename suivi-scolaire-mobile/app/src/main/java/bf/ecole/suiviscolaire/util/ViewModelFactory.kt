package bf.ecole.suiviscolaire.util

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.repository.ApiRepository

/**
 * Fabrique générique pour les ViewModels du projet : ce projet n'utilise
 * pas de framework d'injection de dépendances (Hilt/Koin) pour rester
 * simple à suivre. Chaque ViewModel reçoit directement le [ApiRepository]
 * et le [SessionManager] dont il a besoin.
 */
class ViewModelFactory(
    private val repository: ApiRepository,
    private val sessionManager: SessionManager
) : ViewModelProvider.Factory {

    @Suppress("UNCHECKED_CAST")
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        return when {
            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.login.LoginViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.login.LoginViewModel(repository, sessionManager) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.dashboard.DashboardViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.dashboard.DashboardViewModel(repository, sessionManager) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.notes.NotesViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.notes.NotesViewModel(repository, sessionManager) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.paiements.PaiementsViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.paiements.PaiementsViewModel(repository, sessionManager) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.absences.AbsencesViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.absences.AbsencesViewModel(repository, sessionManager) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.annonces.AnnoncesViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.annonces.AnnoncesViewModel(repository) as T

            modelClass.isAssignableFrom(bf.ecole.suiviscolaire.ui.settings.ChangePasswordViewModel::class.java) ->
                bf.ecole.suiviscolaire.ui.settings.ChangePasswordViewModel(repository) as T

            else -> throw IllegalArgumentException("ViewModel inconnu : ${modelClass.name}")
        }
    }
}

/**
 * Point d'accès unique (Repository + SessionManager), pour éviter de
 * recréer une connexion réseau différente à chaque écran.
 */
object ServiceLocator {
    private var sessionManager: SessionManager? = null
    private var repository: ApiRepository? = null

    fun sessionManager(context: android.content.Context): SessionManager {
        return sessionManager ?: SessionManager(context.applicationContext).also { sessionManager = it }
    }

    fun repository(context: android.content.Context): ApiRepository {
        return repository ?: ApiRepository(sessionManager(context)).also { repository = it }
    }

    fun viewModelFactory(context: android.content.Context): ViewModelFactory {
        return ViewModelFactory(repository(context), sessionManager(context))
    }
}
