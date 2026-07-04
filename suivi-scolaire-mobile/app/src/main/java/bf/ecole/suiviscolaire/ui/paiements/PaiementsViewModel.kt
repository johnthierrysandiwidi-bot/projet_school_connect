package bf.ecole.suiviscolaire.ui.paiements

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.model.PaiementsResponse
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch
import okhttp3.ResponseBody

sealed class PaiementsUiState {
    data object Loading : PaiementsUiState()
    data class Success(val data: PaiementsResponse) : PaiementsUiState()
    data class Error(val message: String) : PaiementsUiState()
}

sealed class DownloadState {
    data object Loading : DownloadState()
    data class Success(val body: ResponseBody, val fileName: String) : DownloadState()
    data class Error(val message: String) : DownloadState()
}

class PaiementsViewModel(
    private val repository: ApiRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableLiveData<PaiementsUiState>()
    val uiState: LiveData<PaiementsUiState> = _uiState

    private val _downloadState = MutableLiveData<DownloadState>()
    val downloadState: LiveData<DownloadState> = _downloadState

    fun load() {
        val eleveId = sessionManager.selectedEleveId
        if (eleveId == -1) {
            _uiState.value = PaiementsUiState.Error("Aucun enfant sélectionné.")
            return
        }

        _uiState.value = PaiementsUiState.Loading

        viewModelScope.launch {
            when (val result = repository.getPaiements(eleveId)) {
                is ApiResult.Success -> _uiState.value = PaiementsUiState.Success(result.data)
                is ApiResult.Error -> _uiState.value = PaiementsUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = PaiementsUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }

    fun downloadRecu(paiementId: Int, reference: String) {
        val eleveId = sessionManager.selectedEleveId
        if (eleveId == -1) return

        _downloadState.value = DownloadState.Loading

        viewModelScope.launch {
            when (val result = repository.downloadRecu(eleveId, paiementId)) {
                is ApiResult.Success -> _downloadState.value =
                    DownloadState.Success(result.data, "recu-$reference.pdf")
                is ApiResult.Error -> _downloadState.value = DownloadState.Error(result.message)
                ApiResult.NetworkError -> _downloadState.value = DownloadState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }
}
