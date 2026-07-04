package bf.ecole.suiviscolaire.ui.absences

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.model.AbsencesResponse
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch

sealed class AbsencesUiState {
    data object Loading : AbsencesUiState()
    data class Success(val data: AbsencesResponse) : AbsencesUiState()
    data class Error(val message: String) : AbsencesUiState()
}

class AbsencesViewModel(
    private val repository: ApiRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableLiveData<AbsencesUiState>()
    val uiState: LiveData<AbsencesUiState> = _uiState

    fun load() {
        val eleveId = sessionManager.selectedEleveId
        if (eleveId == -1) {
            _uiState.value = AbsencesUiState.Error("Aucun enfant sélectionné.")
            return
        }

        _uiState.value = AbsencesUiState.Loading

        viewModelScope.launch {
            when (val result = repository.getAbsences(eleveId)) {
                is ApiResult.Success -> _uiState.value = AbsencesUiState.Success(result.data)
                is ApiResult.Error -> _uiState.value = AbsencesUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = AbsencesUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }
}
