package bf.ecole.suiviscolaire.util

import android.content.Context
import android.content.Intent
import bf.ecole.suiviscolaire.ui.pdf.PdfViewerActivity
import okhttp3.ResponseBody
import java.io.File
import java.io.FileOutputStream

/**
 * Enregistre un PDF (reçu de paiement) dans le cache de l'application puis
 * l'ouvre avec le lecteur PDF intégré de l'application (PdfViewerActivity).
 *
 * On n'utilise plus une application externe pour l'ouvrir : ça évite le cas
 * où l'utilisateur n'a aucun lecteur PDF installé sur son téléphone. Depuis
 * l'écran de visualisation, un bouton "Partager / Enregistrer" permet quand
 * même d'envoyer le fichier vers une autre application si besoin.
 */
object FileDownloader {

    fun saveAndOpenPdf(context: Context, body: ResponseBody, fileName: String, title: String? = null): Boolean {
        return try {
            val dir = File(context.cacheDir, "recus").apply { mkdirs() }
            val file = File(dir, fileName)

            FileOutputStream(file).use { output ->
                body.byteStream().use { input ->
                    input.copyTo(output)
                }
            }

            val intent = Intent(context, PdfViewerActivity::class.java).apply {
                putExtra(PdfViewerActivity.EXTRA_FILE_PATH, file.absolutePath)
                putExtra(PdfViewerActivity.EXTRA_TITLE, title ?: fileName)
            }
            context.startActivity(intent)
            true
        } catch (e: Exception) {
            false
        }
    }
}
