package bf.ecole.suiviscolaire.data.api

import bf.ecole.suiviscolaire.data.SessionManager
import okhttp3.Interceptor
import okhttp3.Response

/**
 * Ajoute automatiquement l'en-tête "Authorization: Bearer <token>" à chaque
 * requête, dès qu'un parent est connecté. La route /login n'a pas besoin de
 * jeton, mais en ajouter un absent ne pose aucun problème (il sera juste nul).
 */
class AuthInterceptor(private val sessionManager: SessionManager) : Interceptor {

    override fun intercept(chain: Interceptor.Chain): Response {
        val original = chain.request()
        val token = sessionManager.token

        val request = if (!token.isNullOrEmpty()) {
            original.newBuilder()
                .addHeader("Authorization", "Bearer $token")
                .addHeader("Accept", "application/json")
                .build()
        } else {
            original.newBuilder()
                .addHeader("Accept", "application/json")
                .build()
        }

        return chain.proceed(request)
    }
}
