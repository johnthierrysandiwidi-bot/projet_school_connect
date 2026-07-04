package bf.ecole.suiviscolaire.util

/**
 * Résultat uniforme d'un appel API, pour éviter de répéter la gestion des
 * erreurs HTTP/réseau dans chaque écran.
 */
sealed class ApiResult<out T> {
    data class Success<T>(val data: T) : ApiResult<T>()
    data class Error(val message: String, val code: Int? = null) : ApiResult<Nothing>()
    data object NetworkError : ApiResult<Nothing>()
}
