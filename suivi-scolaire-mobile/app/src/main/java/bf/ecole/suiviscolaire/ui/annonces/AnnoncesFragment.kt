package bf.ecole.suiviscolaire.ui.annonces

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import bf.ecole.suiviscolaire.databinding.FragmentAnnoncesBinding
import bf.ecole.suiviscolaire.util.ServiceLocator

class AnnoncesFragment : Fragment() {

    private var _binding: FragmentAnnoncesBinding? = null
    private val binding get() = _binding!!

    private val viewModel: AnnoncesViewModel by viewModels {
        ServiceLocator.viewModelFactory(requireContext())
    }

    private var adapter: AnnoncesAdapter? = null

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAnnoncesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.swipeRefresh.setOnRefreshListener { viewModel.load() }

        viewModel.uiState.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false

            when (state) {
                is AnnoncesUiState.Loading -> binding.textError.visibility = View.GONE
                is AnnoncesUiState.Success -> {
                    binding.textError.visibility = View.GONE
                    render(state)
                }
                is AnnoncesUiState.Error -> {
                    binding.textError.text = state.message
                    binding.textError.visibility = View.VISIBLE
                }
            }
        }

        viewModel.load()
    }

    private fun render(state: AnnoncesUiState.Success) {
        val annonces = state.data.annonces.toMutableList()

        if (annonces.isEmpty()) {
            binding.recyclerAnnonces.visibility = View.GONE
            binding.textEmpty.visibility = View.VISIBLE
        } else {
            binding.recyclerAnnonces.visibility = View.VISIBLE
            binding.textEmpty.visibility = View.GONE
            binding.recyclerAnnonces.layoutManager = LinearLayoutManager(requireContext())

            adapter = AnnoncesAdapter(annonces) { annonce ->
                viewModel.marquerLue(annonce.id)
                adapter?.markAsRead(annonce.id)
            }
            binding.recyclerAnnonces.adapter = adapter
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
        adapter = null
    }
}
