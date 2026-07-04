package bf.ecole.suiviscolaire.ui.dashboard

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import bf.ecole.suiviscolaire.R
import bf.ecole.suiviscolaire.data.model.DerniereNote
import bf.ecole.suiviscolaire.databinding.ItemDerniereNoteBinding

class DernieresNotesAdapter(private val notes: List<DerniereNote>) :
    RecyclerView.Adapter<DernieresNotesAdapter.NoteViewHolder>() {

    inner class NoteViewHolder(val binding: ItemDerniereNoteBinding) :
        RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, position: Int): NoteViewHolder {
        val binding = ItemDerniereNoteBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return NoteViewHolder(binding)
    }

    override fun onBindViewHolder(holder: NoteViewHolder, position: Int) {
        val note = notes[position]
        holder.binding.textMatiere.text = note.matiere
        holder.binding.textDate.text = "Trimestre ${note.trimestre} — ${note.date}"
        holder.binding.textValeur.text = "${note.valeur}/${note.bareme}"

        val color = if (note.valeur >= note.bareme / 2.0) R.color.success else R.color.danger
        holder.binding.textValeur.setTextColor(ContextCompat.getColor(holder.itemView.context, color))
    }

    override fun getItemCount(): Int = notes.size
}
