package bf.ecole.suiviscolaire.ui.main

import android.content.Intent
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import android.widget.AdapterView
import android.widget.ArrayAdapter
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import bf.ecole.suiviscolaire.data.model.Eleve
import bf.ecole.suiviscolaire.databinding.ActivityMainBinding
import bf.ecole.suiviscolaire.ui.absences.AbsencesFragment
import bf.ecole.suiviscolaire.ui.annonces.AnnoncesFragment
import bf.ecole.suiviscolaire.ui.dashboard.DashboardFragment
import bf.ecole.suiviscolaire.ui.login.LoginActivity
import bf.ecole.suiviscolaire.ui.notes.NotesFragment
import bf.ecole.suiviscolaire.ui.paiements.PaiementsFragment
import bf.ecole.suiviscolaire.ui.settings.ChangePasswordActivity
import bf.ecole.suiviscolaire.util.ApiResult
import bf.ecole.suiviscolaire.util.ServiceLocator
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private var enfants: List<Eleve> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)

        setupBottomNav()
        loadEnfants(afficherTableauDeBordQuandPret = savedInstanceState == null)
    }

    /**
     * Récupère les enfants du parent connecté et détermine l'élève
     * sélectionné (le seul s'il n'en a qu'un, sinon celui choisi
     * précédemment ou le premier). Si [afficherTableauDeBordQuandPret] est
     * vrai, le Tableau de bord n'est affiché qu'une fois cette sélection
     * faite : sinon, DashboardFragment (et les autres onglets) appellerait
     * son chargement avant que l'identifiant de l'élève soit connu, et
     * afficherait à tort "Aucun enfant sélectionné" au premier lancement.
     */
    private fun loadEnfants(afficherTableauDeBordQuandPret: Boolean = false) {
        val repository = ServiceLocator.repository(this)

        lifecycleScope.launch {
            when (val result = repository.getEnfants()) {
                is ApiResult.Success -> {
                    enfants = result.data
                    val sessionManager = ServiceLocator.sessionManager(this@MainActivity)

                    if (enfants.size > 1) {
                        setupSpinner(enfants)
                    } else if (enfants.size == 1 && sessionManager.selectedEleveId != enfants.first().id) {
                        sessionManager.selectedEleveId = enfants.first().id
                    }
                }
                else -> { /* L'écran courant affichera sa propre erreur réseau au chargement. */ }
            }

            if (afficherTableauDeBordQuandPret) {
                showFragment(DashboardFragment())
            }
        }
    }

    private fun setupSpinner(enfants: List<Eleve>) {
        binding.spinnerEnfants.visibility = android.view.View.VISIBLE

        val labels = enfants.map { "${it.nom} ${it.prenom} — ${it.classe?.nom ?: ""}" }
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, labels)
        binding.spinnerEnfants.adapter = adapter

        val sessionManager = ServiceLocator.sessionManager(this)
        val currentIndex = enfants.indexOfFirst { it.id == sessionManager.selectedEleveId }
        if (currentIndex >= 0) {
            binding.spinnerEnfants.setSelection(currentIndex)
        } else {
            // L'enfant précédemment sélectionné n'est plus lié à ce compte
            // (ex: modifié par le gestionnaire) : on retombe sur le premier.
            sessionManager.selectedEleveId = enfants.first().id
        }

        binding.spinnerEnfants.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>?, view: android.view.View?, position: Int, id: Long) {
                val selected = enfants[position]
                if (selected.id != sessionManager.selectedEleveId) {
                    sessionManager.selectedEleveId = selected.id
                    // Recharge l'écran courant avec les données du nouvel enfant sélectionné.
                    showFragment(currentFragment())
                }
            }

            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }
    }

    private fun setupBottomNav() {
        binding.bottomNav.setOnItemSelectedListener { item ->
            val fragment = when (item.itemId) {
                bf.ecole.suiviscolaire.R.id.nav_dashboard -> DashboardFragment()
                bf.ecole.suiviscolaire.R.id.nav_notes -> NotesFragment()
                bf.ecole.suiviscolaire.R.id.nav_paiements -> PaiementsFragment()
                bf.ecole.suiviscolaire.R.id.nav_absences -> AbsencesFragment()
                bf.ecole.suiviscolaire.R.id.nav_annonces -> AnnoncesFragment()
                else -> DashboardFragment()
            }
            showFragment(fragment)
            true
        }
    }

    private fun currentFragment(): androidx.fragment.app.Fragment {
        return supportFragmentManager.findFragmentById(binding.fragmentContainer.id) ?: DashboardFragment()
    }

    private fun showFragment(fragment: androidx.fragment.app.Fragment) {
        supportFragmentManager.beginTransaction()
            .replace(binding.fragmentContainer.id, fragment)
            .commit()
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        menuInflater.inflate(bf.ecole.suiviscolaire.R.menu.main_menu, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        return when (item.itemId) {
            bf.ecole.suiviscolaire.R.id.action_change_password -> {
                startActivity(Intent(this, ChangePasswordActivity::class.java))
                true
            }
            bf.ecole.suiviscolaire.R.id.action_logout -> {
                logout()
                true
            }
            else -> super.onOptionsItemSelected(item)
        }
    }

    private fun logout() {
        val repository = ServiceLocator.repository(this)
        val sessionManager = ServiceLocator.sessionManager(this)

        lifecycleScope.launch {
            // On tente de révoquer le jeton côté serveur, mais on déconnecte
            // l'utilisateur localement même si la requête échoue (pas de
            // réseau, jeton déjà expiré, etc).
            runCatching { repository.logout() }

            sessionManager.clear()
            startActivity(Intent(this@MainActivity, LoginActivity::class.java))
            finish()
        }
    }
}
