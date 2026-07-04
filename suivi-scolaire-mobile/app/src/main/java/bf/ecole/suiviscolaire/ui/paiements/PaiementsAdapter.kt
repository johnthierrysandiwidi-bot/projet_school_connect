package bf.ecole.suiviscolaire.ui.paiements

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import bf.ecole.suiviscolaire.data.model.Paiement
import bf.ecole.suiviscolaire.databinding.ItemPaiementBinding
import java.text.NumberFormat
import java.util.Locale

class PaiementsAdapter(
    private val paiements: List<Paiement>,
    private val onDownloadClick: (Paiement) -> Unit
) : RecyclerView.Adapter<PaiementsAdapter.PaiementViewHolder>() {

    inner class PaiementViewHolder(val binding: ItemPaiementBinding) :
        RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, position: Int): PaiementViewHolder {
        val binding = ItemPaiementBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return PaiementViewHolder(binding)
    }

    override fun onBindViewHolder(holder: PaiementViewHolder, position: Int) {
        val paiement = paiements[position]
        val format = NumberFormat.getNumberInstance(Locale.FRANCE)

        holder.binding.textMontant.text = "${format.format(paiement.montant)} FCFA"
        holder.binding.textDetails.text = "${paiement.datePaiement} — ${paiement.modePaiement}"
        holder.binding.textReference.text = paiement.reference
        holder.binding.btnDownload.setOnClickListener { onDownloadClick(paiement) }
    }

    override fun getItemCount(): Int = paiements.size
}
