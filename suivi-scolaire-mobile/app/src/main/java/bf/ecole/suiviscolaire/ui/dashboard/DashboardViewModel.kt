package bf.ecole.suiviscolaire.ui.dashboard

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.model.DashboardResponse
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch

sealed class DashboardUiState {
    data object Loading : DashboardUiState()
    data class Success(val data: DashboardResponse) : DashboardUiState()
    data class Error(val message: String) : DashboardUiState()
}

class DashboardViewModel(
    private val repository: ApiRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableLiveData<DashboardUiState>()
    val uiState: LiveData<DashboardUiState> = _uiState

    fun load(trimestre: Int = 1) {
        val eleveId = sessionManager.selectedEleveId
        if (eleveId == -1) {
            _uiState.value = DashboardUiState.Error("Aucun enfant sélectionné.")
            return
        }

        _uiState.value = DashboardUiState.Loading

        viewModelScope.launch {
            when (val result = repository.getDashboard(eleveId, trimestre)) {
                is ApiResult.Success -> _uiState.value = DashboardUiState.Success(result.data)
                is ApiResult.Error -> _uiState.value = DashboardUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = DashboardUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }
}
