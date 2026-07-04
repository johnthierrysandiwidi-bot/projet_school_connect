package bf.ecole.suiviscolaire.data.repository

import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.api.ApiClient
import bf.ecole.suiviscolaire.data.api.ApiService
import bf.ecole.suiviscolaire.data.model.AbsencesResponse
import bf.ecole.suiviscolaire.data.model.AnnoncesResponse
import bf.ecole.suiviscolaire.data.model.ChangePasswordRequest
import bf.ecole.suiviscolaire.data.model.DashboardResponse
import bf.ecole.suiviscolaire.data.model.Eleve
import bf.ecole.suiviscolaire.data.model.LoginRequest
import bf.ecole.suiviscolaire.data.model.LoginResponse
import bf.ecole.suiviscolaire.data.model.MessageResponse
import bf.ecole.suiviscolaire.data.model.NotesResponse
import bf.ecole.suiviscolaire.data.model.PaiementsResponse
import bf.ecole.suiviscolaire.util.ApiResult
import com.google.gson.Gson
import com.google.gson.JsonObject
import okhttp3.ResponseBody
import retrofit2.Response
import java.io.IOException

/**
 * Point d'accès unique à l'API depuis les ViewModels. Convertit chaque appel
 * Retrofit en [ApiResult], en lisant le message d'erreur JSON renvoyé par
 * Laravel ({"message": "..."}) quand la requête échoue.
 */
class ApiRepository(private val sessionManager: SessionManager) {

    private val service: ApiService = ApiClient.create(sessionManager)

    suspend fun login(email: String, password: String): ApiResult<LoginResponse> =
        safeCall { service.login(LoginRequest(email, password)) }

    suspend fun logout(): ApiResult<MessageResponse> =
        safeCall { service.logout() }

    suspend fun changePassword(current: String, new: String, confirmation: String): ApiResult<MessageResponse> =
        safeCall { service.changePassword(ChangePasswordRequest(current, new, confirmation)) }

    suspend fun getEnfants(): ApiResult<List<Eleve>> =
        when (val result = safeCall { service.getEnfants() }) {
            is ApiResult.Success -> ApiResult.Success(result.data.data)
            is ApiResult.Error -> result
            ApiResult.NetworkError -> ApiResult.NetworkError
        }

    suspend fun getDashboard(eleveId: Int, trimestre: Int): ApiResult<DashboardResponse> =
        safeCall { service.getDashboard(eleveId, trimestre) }

    suspend fun getNotes(eleveId: Int): ApiResult<NotesResponse> =
        safeCall { service.getNotes(eleveId) }

    suspend fun getPaiements(eleveId: Int): ApiResult<PaiementsResponse> =
        safeCall { service.getPaiements(eleveId) }

    suspend fun downloadRecu(eleveId: Int, paiementId: Int): ApiResult<ResponseBody> =
        safeCall { service.downloadRecu(eleveId, paiementId) }

    suspend fun getAbsences(eleveId: Int): ApiResult<AbsencesResponse> =
        safeCall { service.getAbsences(eleveId) }

    suspend fun getAnnonces(): ApiResult<AnnoncesResponse> =
        safeCall { service.getAnnonces() }

    suspend fun marquerAnnonceLue(annonceId: Int): ApiResult<MessageResponse> =
        safeCall { service.marquerAnnonceLue(annonceId) }

    /**
     * Exécute un appel Retrofit et le transforme en [ApiResult], en gérant
     * de façon centralisée les codes d'erreur HTTP et les pannes réseau.
     */
    private suspend fun <T> safeCall(call: suspend () -> Response<T>): ApiResult<T> {
        return try {
            val response = call()
            val body = response.body()

            if (response.isSuccessful && body != null) {
                ApiResult.Success(body)
            } else {
                ApiResult.Error(extractErrorMessage(response), response.code())
            }
        } catch (e: IOException) {
            ApiResult.NetworkError
        } catch (e: Exception) {
            ApiResult.Error(e.message ?: "Erreur inconnue")
        }
    }

    private fun <T> extractErrorMessage(response: Response<T>): String {
        return try {
            val errorJson = response.errorBody()?.string()
            val parsed = Gson().fromJson(errorJson, JsonObject::class.java)
            parsed?.get("message")?.asString ?: "Erreur ${response.code()}"
        } catch (e: Exception) {
            "Erreur ${response.code()}"
        }
    }
}
