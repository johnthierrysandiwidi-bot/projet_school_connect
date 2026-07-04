package bf.ecole.suiviscolaire.ui.absences

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import bf.ecole.suiviscolaire.R
import bf.ecole.suiviscolaire.data.model.Absence
import bf.ecole.suiviscolaire.databinding.ItemAbsenceBinding

class AbsencesAdapter(private val absences: List<Absence>) :
    RecyclerView.Adapter<AbsencesAdapter.AbsenceViewHolder>() {

    inner class AbsenceViewHolder(val binding: ItemAbsenceBinding) :
        RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, position: Int): AbsenceViewHolder {
        val binding = ItemAbsenceBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return AbsenceViewHolder(binding)
    }

    override fun onBindViewHolder(holder: AbsenceViewHolder, position: Int) {
        val absence = absences[position]
        val context = holder.itemView.context

        holder.binding.textDate.text = absence.dateAbsence
        holder.binding.textMotif.text = absence.motif ?: "Motif non renseigné"

        if (absence.justifiee) {
            holder.binding.badgeJustifiee.text = context.getString(R.string.badge_justifiee)
            holder.binding.badgeJustifiee.background.mutate().setTint(ContextCompat.getColor(context, R.color.success))
        } else {
            holder.binding.badgeJustifiee.text = context.getString(R.string.badge_non_justifiee)
            holder.binding.badgeJustifiee.background.mutate().setTint(ContextCompat.getColor(context, R.color.danger))
        }
    }

    override fun getItemCount(): Int = absences.size
}
