package bf.ecole.suiviscolaire.ui.notes

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import bf.ecole.suiviscolaire.R
import bf.ecole.suiviscolaire.data.model.MatiereNote
import bf.ecole.suiviscolaire.databinding.ItemMatiereNoteBinding

class MatieresAdapter(private val matieres: List<MatiereNote>) :
    RecyclerView.Adapter<MatieresAdapter.MatiereViewHolder>() {

    inner class MatiereViewHolder(val binding: ItemMatiereNoteBinding) :
        RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, position: Int): MatiereViewHolder {
        val binding = ItemMatiereNoteBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return MatiereViewHolder(binding)
    }

    override fun onBindViewHolder(holder: MatiereViewHolder, position: Int) {
        val matiere = matieres[position]
        holder.binding.textMatiere.text = matiere.matiere
        holder.binding.textCoefficient.text = "Coefficient ${matiere.coefficient}"
        holder.binding.textValeur.text = matiere.valeur?.let { "$it/${matiere.bareme}" }
            ?: holder.itemView.context.getString(R.string.no_grade)
    }

    override fun getItemCount(): Int = matieres.size
}
