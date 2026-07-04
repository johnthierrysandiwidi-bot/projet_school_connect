package bf.ecole.suiviscolaire.data.api

import bf.ecole.suiviscolaire.data.model.AbsencesResponse
import bf.ecole.suiviscolaire.data.model.AnnoncesResponse
import bf.ecole.suiviscolaire.data.model.ChangePasswordRequest
import bf.ecole.suiviscolaire.data.model.DashboardResponse
import bf.ecole.suiviscolaire.data.model.EnfantsListResponse
import bf.ecole.suiviscolaire.data.model.LoginRequest
import bf.ecole.suiviscolaire.data.model.LoginResponse
import bf.ecole.suiviscolaire.data.model.MessageResponse
import bf.ecole.suiviscolaire.data.model.NotesResponse
import bf.ecole.suiviscolaire.data.model.PaiementsResponse
import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query
import retrofit2.http.Streaming

/**
 * Décrit chaque route de l'API REST exposée par le backend Laravel
 * (voir le README.md de ce projet mobile et celui du projet Laravel pour le
 * détail des routes et de l'authentification par jeton Sanctum).
 */
interface ApiService {

    @POST("login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("logout")
    suspend fun logout(): Response<MessageResponse>

    @PUT("password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): Response<MessageResponse>

    @GET("enfants")
    suspend fun getEnfants(): Response<EnfantsListResponse>

    @GET("enfants/{id}")
    suspend fun getDashboard(
        @Path("id") eleveId: Int,
        @Query("trimestre") trimestre: Int
    ): Response<DashboardResponse>

    @GET("enfants/{id}/notes")
    suspend fun getNotes(@Path("id") eleveId: Int): Response<NotesResponse>

    @GET("enfants/{id}/paiements")
    suspend fun getPaiements(@Path("id") eleveId: Int): Response<PaiementsResponse>

    @Streaming
    @GET("enfants/{eleveId}/paiements/{paiementId}/recu")
    suspend fun downloadRecu(
        @Path("eleveId") eleveId: Int,
        @Path("paiementId") paiementId: Int
    ): Response<ResponseBody>

    @GET("enfants/{id}/absences")
    suspend fun getAbsences(@Path("id") eleveId: Int): Response<AbsencesResponse>

    @GET("annonces")
    suspend fun getAnnonces(): Response<AnnoncesResponse>

    @POST("annonces/{id}/lue")
    suspend fun marquerAnnonceLue(@Path("id") annonceId: Int): Response<MessageResponse>
}
