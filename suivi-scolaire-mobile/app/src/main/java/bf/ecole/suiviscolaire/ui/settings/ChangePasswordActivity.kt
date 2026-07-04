package bf.ecole.suiviscolaire.ui.settings

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import bf.ecole.suiviscolaire.R
import bf.ecole.suiviscolaire.databinding.ActivityChangePasswordBinding
import bf.ecole.suiviscolaire.util.ServiceLocator

class ChangePasswordActivity : AppCompatActivity() {

    private lateinit var binding: ActivityChangePasswordBinding

    private val viewModel: ChangePasswordViewModel by viewModels {
        ServiceLocator.viewModelFactory(this)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityChangePasswordBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        title = getString(R.string.title_change_password)

        binding.btnSave.setOnClickListener {
            viewModel.changePassword(
                binding.editCurrentPassword.text?.toString().orEmpty(),
                binding.editNewPassword.text?.toString().orEmpty(),
                binding.editConfirmPassword.text?.toString().orEmpty()
            )
        }

        viewModel.uiState.observe(this) { state ->
            when (state) {
                is ChangePasswordUiState.Idle -> showLoading(false)
                is ChangePasswordUiState.Loading -> showLoading(true)
                is ChangePasswordUiState.Success -> {
                    showLoading(false)
                    Toast.makeText(this, getString(R.string.password_changed_success), Toast.LENGTH_LONG).show()
                    finish()
                }
                is ChangePasswordUiState.Error -> {
                    showLoading(false)
                    binding.textError.text = state.message
                    binding.textError.visibility = View.VISIBLE
                }
            }
        }
    }

    private fun showLoading(loading: Boolean) {
        binding.progressBar.visibility = if (loading) View.VISIBLE else View.GONE
        binding.btnSave.isEnabled = !loading
        if (loading) binding.textError.visibility = View.GONE
    }

    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }
}
