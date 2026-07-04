package bf.ecole.suiviscolaire.ui.dashboard

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import bf.ecole.suiviscolaire.R
import bf.ecole.suiviscolaire.databinding.FragmentDashboardBinding
import bf.ecole.suiviscolaire.util.ServiceLocator
import com.bumptech.glide.Glide

class DashboardFragment : Fragment() {

    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!

    private val viewModel: DashboardViewModel by viewModels {
        ServiceLocator.viewModelFactory(requireContext())
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentDashboardBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.swipeRefresh.setOnRefreshListener { viewModel.load() }

        viewModel.uiState.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false

            when (state) {
                is DashboardUiState.Loading -> {
                    binding.textError.visibility = View.GONE
                }
                is DashboardUiState.Success -> {
                    binding.textError.visibility = View.GONE
                    render(state)
                }
                is DashboardUiState.Error -> {
                    binding.textError.text = state.message
                    binding.textError.visibility = View.VISIBLE
                }
                else -> {}
            }
        }

        viewModel.load()
    }

    private fun render(state: DashboardUiState.Success) {
        val data = state.data
        val eleve = data.eleve

        binding.textNomEleve.text = eleve.nomComplet
        binding.textClasseEleve.text = eleve.classe?.nom ?: ""

        Glide.with(this)
            .load(eleve.photoUrl)
            .placeholder(R.drawable.bg_avatar_placeholder)
            .error(R.drawable.bg_avatar_placeholder)
            .circleCrop()
            .into(binding.imgPhoto)

        binding.textMoyenne.text = data.moyenneGenerale?.let { "$it/10" } ?: "—"
        binding.textRang.text = if (data.rang != null) {
            getString(R.string.rank_format, data.rang, data.totalEleves)
        } else {
            "—"
        }

        if (data.dernieresNotes.isEmpty()) {
            binding.recyclerDernieresNotes.visibility = View.GONE
            binding.textEmptyNotes.visibility = View.VISIBLE
        } else {
            binding.recyclerDernieresNotes.visibility = View.VISIBLE
            binding.textEmptyNotes.visibility = View.GONE
            binding.recyclerDernieresNotes.layoutManager = LinearLayoutManager(requireContext())
            binding.recyclerDernieresNotes.adapter = DernieresNotesAdapter(data.dernieresNotes)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
