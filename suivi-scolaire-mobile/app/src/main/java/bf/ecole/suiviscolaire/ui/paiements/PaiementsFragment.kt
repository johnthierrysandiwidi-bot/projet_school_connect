package bf.ecole.suiviscolaire.ui.paiements

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import bf.ecole.suiviscolaire.databinding.FragmentPaiementsBinding
import bf.ecole.suiviscolaire.util.FileDownloader
import bf.ecole.suiviscolaire.util.ServiceLocator
import java.text.NumberFormat
import java.util.Locale

class PaiementsFragment : Fragment() {

    private var _binding: FragmentPaiementsBinding? = null
    private val binding get() = _binding!!

    private val viewModel: PaiementsViewModel by viewModels {
        ServiceLocator.viewModelFactory(requireContext())
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
    ): View {
        _binding = FragmentPaiementsBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.swipeRefresh.setOnRefreshListener { viewModel.load() }

        viewModel.uiState.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false

            when (state) {
                is PaiementsUiState.Loading -> binding.textError.visibility = View.GONE
                is PaiementsUiState.Success -> {
                    binding.textError.visibility = View.GONE
                    render(state)
                }
                is PaiementsUiState.Error -> {
                    binding.textError.text = state.message
                    binding.textError.visibility = View.VISIBLE
                }
            }
        }

        viewModel.downloadState.observe(viewLifecycleOwner) { state ->
            binding.swipeRefresh.isRefreshing = false
            when (state) {
                is DownloadState.Loading -> {
                    binding.swipeRefresh.isRefreshing = true
                }
                is DownloadState.Success -> {
                    val opened = FileDownloader.saveAndOpenPdf(requireContext(), state.body, state.fileName)
                    if (!opened) {
                        Toast.makeText(
                            requireContext(),
                            "Aucun lecteur PDF installé. Installez une application PDF (ex. Adobe Acrobat) pour ouvrir le reçu.",
                            Toast.LENGTH_LONG
                        ).show()
                    }
                }
                is DownloadState.Error -> {
                    Toast.makeText(requireContext(), state.message, Toast.LENGTH_SHORT).show()
                }
            }
        }

        viewModel.load()
    }

    private fun render(state: PaiementsUiState.Success) {
        val data = state.data
        val format = NumberFormat.getNumberInstance(Locale.FRANCE)

        binding.textFraisTotal.text = "${format.format(data.fraisTotal)} FCFA"
        binding.textMontantPaye.text = "${format.format(data.montantPaye)} FCFA"
        binding.textResteAPayer.text = "${format.format(data.resteAPayer)} FCFA"

        if (data.paiements.isEmpty()) {
            binding.recyclerPaiements.visibility = View.GONE
            binding.textEmpty.visibility = View.VISIBLE
        } else {
            binding.recyclerPaiements.visibility = View.VISIBLE
            binding.textEmpty.visibility = View.GONE
            binding.recyclerPaiements.layoutManager = LinearLayoutManager(requireContext())
            binding.recyclerPaiements.adapter = PaiementsAdapter(data.paiements) { paiement ->
                viewModel.downloadRecu(paiement.id, paiement.reference)
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
