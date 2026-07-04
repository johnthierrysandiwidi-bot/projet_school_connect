package bf.ecole.suiviscolaire.ui.annonces

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import bf.ecole.suiviscolaire.data.model.Annonce
import bf.ecole.suiviscolaire.databinding.ItemAnnonceBinding

class AnnoncesAdapter(
    private val annonces: MutableList<Annonce>,
    private val onAnnonceClick: (Annonce) -> Unit
) : RecyclerView.Adapter<AnnoncesAdapter.AnnonceViewHolder>() {

    inner class AnnonceViewHolder(val binding: ItemAnnonceBinding) :
        RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, position: Int): AnnonceViewHolder {
        val binding = ItemAnnonceBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return AnnonceViewHolder(binding)
    }

    override fun onBindViewHolder(holder: AnnonceViewHolder, position: Int) {
        val annonce = annonces[position]

        holder.binding.textIcone.text = annonce.icone
        holder.binding.textTitre.text = annonce.titre
        holder.binding.textContenu.text = annonce.contenu
        holder.binding.textDate.text = annonce.classe?.let { "${annonce.datePublication} — Classe $it" }
            ?: "${annonce.datePublication} — Toute l'école"

        holder.binding.badgeNonLue.visibility = if (annonce.lu) View.GONE else View.VISIBLE

        holder.binding.root.setOnClickListener {
            if (!annonce.lu) {
                onAnnonceClick(annonce)
            }
        }
    }

    override fun getItemCount(): Int = annonces.size

    /** Met à jour localement une annonce comme lue, sans recharger toute la liste. */
    fun markAsRead(annonceId: Int) {
        val index = annonces.indexOfFirst { it.id == annonceId }
        if (index != -1) {
            annonces[index] = annonces[index].copy(lu = true)
            notifyItemChanged(index)
        }
    }
}
