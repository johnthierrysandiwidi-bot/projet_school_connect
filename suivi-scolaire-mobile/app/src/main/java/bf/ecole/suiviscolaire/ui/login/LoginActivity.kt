package bf.ecole.suiviscolaire.ui.login

import android.content.Intent
import android.os.Bundle
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import bf.ecole.suiviscolaire.databinding.ActivityLoginBinding
import bf.ecole.suiviscolaire.ui.main.MainActivity
import bf.ecole.suiviscolaire.util.ServiceLocator

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding

    private val viewModel: LoginViewModel by viewModels {
        ServiceLocator.viewModelFactory(this)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Déjà connecté ? On passe directement au tableau de bord.
        if (ServiceLocator.sessionManager(this).isLoggedIn()) {
            goToMain()
            return
        }

        binding.btnLogin.setOnClickListener {
            val email = binding.editEmail.text?.toString()?.trim().orEmpty()
            val password = binding.editPassword.text?.toString().orEmpty()
            viewModel.login(email, password)
        }

        viewModel.uiState.observe(this) { state ->
            when (state) {
                is LoginUiState.Idle -> showLoading(false)
                is LoginUiState.Loading -> showLoading(true)
                is LoginUiState.Success -> {
                    showLoading(false)
                    goToMain()
                }
                is LoginUiState.Error -> {
                    showLoading(false)
                    binding.textError.text = state.message
                    binding.textError.visibility = android.view.View.VISIBLE
                }
            }
        }
    }

    private fun showLoading(loading: Boolean) {
        binding.progressBar.visibility = if (loading) android.view.View.VISIBLE else android.view.View.GONE
        binding.btnLogin.isEnabled = !loading
        if (loading) {
            binding.textError.visibility = android.view.View.GONE
        }
    }

    private fun goToMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }
}
