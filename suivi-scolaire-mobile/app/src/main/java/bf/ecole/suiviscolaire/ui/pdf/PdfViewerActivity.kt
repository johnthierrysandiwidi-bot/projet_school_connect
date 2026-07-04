package bf.ecole.suiviscolaire.ui.pdf

import android.content.Intent
import android.graphics.Bitmap
import android.graphics.Color
import android.graphics.pdf.PdfRenderer
import android.net.Uri
import android.os.Bundle
import android.os.ParcelFileDescriptor
import android.widget.ImageView
import android.widget.LinearLayout
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.FileProvider
import bf.ecole.suiviscolaire.databinding.ActivityPdfViewerBinding
import java.io.File

/**
 * Affiche un fichier PDF (reçu de paiement) directement dans l'application,
 * en s'appuyant sur l'API native android.graphics.pdf.PdfRenderer.
 *
 * Avantage : ça fonctionne toujours, même si aucune application de lecture
 * PDF (Adobe Acrobat, Google Drive, etc.) n'est installée sur l'appareil.
 * Un bouton "Partager / Enregistrer" permet ensuite à l'utilisateur d'envoyer
 * le fichier vers une autre application s'il le souhaite.
 */
class PdfViewerActivity : AppCompatActivity() {

    private lateinit var binding: ActivityPdfViewerBinding
    private var pdfRenderer: PdfRenderer? = null
    private var fileDescriptor: ParcelFileDescriptor? = null
    private lateinit var pdfFile: File

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityPdfViewerBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val filePath = intent.getStringExtra(EXTRA_FILE_PATH)
        if (filePath == null) {
            finish()
            return
        }
        pdfFile = File(filePath)

        setSupportActionBar(binding.toolbar)
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        title = intent.getStringExtra(EXTRA_TITLE) ?: pdfFile.name

        binding.buttonShare.setOnClickListener { shareReceipt() }

        renderPdf()
    }

    private fun renderPdf() {
        try {
            fileDescriptor = ParcelFileDescriptor.open(pdfFile, ParcelFileDescriptor.MODE_READ_ONLY)
            val renderer = PdfRenderer(fileDescriptor!!)
            pdfRenderer = renderer

            binding.pagesContainer.removeAllViews()
            val marginPx = (16 * resources.displayMetrics.density).toInt()

            for (i in 0 until renderer.pageCount) {
                val page = renderer.openPage(i)
                val bitmap = Bitmap.createBitmap(
                    page.width * 2,
                    page.height * 2,
                    Bitmap.Config.ARGB_8888
                )
                bitmap.eraseColor(Color.WHITE)
                page.render(bitmap, null, null, PdfRenderer.Page.RENDER_MODE_FOR_DISPLAY)
                page.close()

                val imageView = ImageView(this).apply {
                    layoutParams = LinearLayout.LayoutParams(
                        LinearLayout.LayoutParams.MATCH_PARENT,
                        LinearLayout.LayoutParams.WRAP_CONTENT
                    ).also { it.bottomMargin = marginPx }
                    adjustViewBounds = true
                    setImageBitmap(bitmap)
                }
                binding.pagesContainer.addView(imageView)
            }
        } catch (e: Exception) {
            Toast.makeText(
                this,
                "Impossible d'afficher le reçu : ${e.message}",
                Toast.LENGTH_LONG
            ).show()
            finish()
        }
    }

    private fun shareReceipt() {
        try {
            val uri: Uri = FileProvider.getUriForFile(
                this,
                "$packageName.fileprovider",
                pdfFile
            )
            val intent = Intent(Intent.ACTION_SEND).apply {
                type = "application/pdf"
                putExtra(Intent.EXTRA_STREAM, uri)
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            }
            startActivity(Intent.createChooser(intent, "Partager ou enregistrer le reçu"))
        } catch (e: Exception) {
            Toast.makeText(this, "Impossible de partager le reçu.", Toast.LENGTH_SHORT).show()
        }
    }

    override fun onSupportNavigateUp(): Boolean {
        finish()
        return true
    }

    override fun onDestroy() {
        super.onDestroy()
        pdfRenderer?.close()
        fileDescriptor?.close()
    }

    companion object {
        const val EXTRA_FILE_PATH = "extra_file_path"
        const val EXTRA_TITLE = "extra_title"
    }
}
