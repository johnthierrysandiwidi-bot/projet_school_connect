package bf.ecole.suiviscolaire.ui.annonces

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.model.AnnoncesResponse
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch

sealed class AnnoncesUiState {
    data object Loading : AnnoncesUiState()
    data class Success(val data: AnnoncesResponse) : AnnoncesUiState()
    data class Error(val message: String) : AnnoncesUiState()
}

class AnnoncesViewModel(private val repository: ApiRepository) : ViewModel() {

    private val _uiState = MutableLiveData<AnnoncesUiState>()
    val uiState: LiveData<AnnoncesUiState> = _uiState

    fun load() {
        _uiState.value = AnnoncesUiState.Loading

        viewModelScope.launch {
            when (val result = repository.getAnnonces()) {
                is ApiResult.Success -> _uiState.value = AnnoncesUiState.Success(result.data)
                is ApiResult.Error -> _uiState.value = AnnoncesUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = AnnoncesUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }

    /** Marque une annonce comme lue côté serveur (l'UI est déjà mise à jour localement). */
    fun marquerLue(annonceId: Int) {
        viewModelScope.launch {
            repository.marquerAnnonceLue(annonceId)
        }
    }
}
