package bf.ecole.suiviscolaire.ui.notes

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.SessionManager
import bf.ecole.suiviscolaire.data.model.NotesResponse
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch

sealed class NotesUiState {
    data object Loading : NotesUiState()
    data class Success(val data: NotesResponse) : NotesUiState()
    data class Error(val message: String) : NotesUiState()
}

class NotesViewModel(
    private val repository: ApiRepository,
    private val sessionManager: SessionManager
) : ViewModel() {

    private val _uiState = MutableLiveData<NotesUiState>()
    val uiState: LiveData<NotesUiState> = _uiState

    fun load() {
        val eleveId = sessionManager.selectedEleveId
        if (eleveId == -1) {
            _uiState.value = NotesUiState.Error("Aucun enfant sélectionné.")
            return
        }

        _uiState.value = NotesUiState.Loading

        viewModelScope.launch {
            when (val result = repository.getNotes(eleveId)) {
                is ApiResult.Success -> _uiState.value = NotesUiState.Success(result.data)
                is ApiResult.Error -> _uiState.value = NotesUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = NotesUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }
}
