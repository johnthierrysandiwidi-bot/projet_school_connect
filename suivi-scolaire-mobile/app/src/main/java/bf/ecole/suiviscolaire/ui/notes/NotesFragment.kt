package bf.ecole.suiviscolaire.ui.notes

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import bf.ecole.suiviscolaire.data.model.NotesResponse
import bf.ecole.suiviscolaire.databinding.FragmentNotesBinding
import bf.ecole.suiviscolaire.util.ServiceLocator
import com.google.android.material.tabs.TabLayout

class NotesFragment : Fragment() {

    private var _binding: FragmentNotesBinding? = null
    private val binding get() = _binding!!

    private val viewModel: NotesViewModel by viewModels {
        ServiceLocator.viewModelFactory(requireContext())
    }

    private var notesData: NotesResponse? = null
    private var selectedTrimestreIndex = 0

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentNotesBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.tabTrimestres.addTab(binding.tabTrimestres.newTab().setText("Trimestre 1"))
        binding.tabTrimestres.addTab(binding.tabTrimestres.newTab().setText("Trimestre 2"))
        binding.tabTrimestres.addTab(binding.tabTrimestres.newTab().setText("Trimestre 3"))

        binding.tabTrimestres.addOnTabSelectedListener(object : TabLayout.OnTabSelectedListener {
            override fun onTabSelected(tab: TabLayout.Tab) {
                selectedTrimestreIndex = tab.position
                renderSelectedTrimestre()
            }
            override fun onTabUnselected(tab: TabLayout.Tab) {}
            override fun onTabReselected(tab: TabLayout.Tab) {}
        })

        binding.swipeRefresh.setOnRefreshListener { viewModel.load() }

        viewModel.uiState.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false

            when (state) {
                is NotesUiState.Loading -> binding.textError.visibility = View.GONE
                is NotesUiState.Success -> {
                    binding.textError.visibility = View.GONE
                    notesData = state.data
                    renderSelectedTrimestre()
                }
                is NotesUiState.Error -> {
                    binding.textError.text = state.message
                    binding.textError.visibility = View.VISIBLE
                }
            }
        }

        viewModel.load()
    }

    private fun renderSelectedTrimestre() {
        val trimestre = notesData?.trimestres?.getOrNull(selectedTrimestreIndex) ?: return

        binding.textMoyenneTrimestre.text = trimestre.moyenne?.let { "$it/10" } ?: "—"
        binding.recyclerMatieres.layoutManager = LinearLayoutManager(requireContext())
        binding.recyclerMatieres.adapter = MatieresAdapter(trimestre.matieres)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
