package bf.ecole.suiviscolaire.ui.settings

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import bf.ecole.suiviscolaire.data.repository.ApiRepository
import bf.ecole.suiviscolaire.util.ApiResult
import kotlinx.coroutines.launch

sealed class ChangePasswordUiState {
    data object Idle : ChangePasswordUiState()
    data object Loading : ChangePasswordUiState()
    data object Success : ChangePasswordUiState()
    data class Error(val message: String) : ChangePasswordUiState()
}

class ChangePasswordViewModel(private val repository: ApiRepository) : ViewModel() {

    private val _uiState = MutableLiveData<ChangePasswordUiState>(ChangePasswordUiState.Idle)
    val uiState: LiveData<ChangePasswordUiState> = _uiState

    fun changePassword(current: String, new: String, confirmation: String) {
        if (current.isBlank() || new.isBlank() || confirmation.isBlank()) {
            _uiState.value = ChangePasswordUiState.Error("Veuillez remplir tous les champs.")
            return
        }
        if (new != confirmation) {
            _uiState.value = ChangePasswordUiState.Error("Les mots de passe ne correspondent pas.")
            return
        }
        if (new.length < 6) {
            _uiState.value = ChangePasswordUiState.Error("Le nouveau mot de passe doit contenir au moins 6 caractères.")
            return
        }

        _uiState.value = ChangePasswordUiState.Loading

        viewModelScope.launch {
            when (val result = repository.changePassword(current, new, confirmation)) {
                is ApiResult.Success -> _uiState.value = ChangePasswordUiState.Success
                is ApiResult.Error -> _uiState.value = ChangePasswordUiState.Error(result.message)
                ApiResult.NetworkError -> _uiState.value = ChangePasswordUiState.Error(
                    "Impossible de contacter le serveur. Vérifiez votre connexion."
                )
            }
        }
    }
}
