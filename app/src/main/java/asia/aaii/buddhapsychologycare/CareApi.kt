package asia.aaii.buddhapsychologycare

import android.os.Handler
import android.os.Looper
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

/** HTTPS JSON client: tokens are kept in memory only; redirects, downgrade and unbounded responses are blocked. */
object CareApi {
    private val worker = Executors.newSingleThreadExecutor()
    private val main = Handler(Looper.getMainLooper())
    fun request(path: String, method: String = "GET", bearer: String? = null,
                body: JSONObject? = null, onResult: (JSONObject) -> Unit) {
        worker.execute {
            val response = try {
                val url = URL(BuildConfig.API_BASE_URL + path)
                require(url.protocol == "https")
                val connection = (url.openConnection() as HttpURLConnection)
                connection.instanceFollowRedirects = false // Never silently follow redirects or HTTP downgrade.
                try {
                    connection.requestMethod = method
                    connection.connectTimeout = 9000
                    connection.readTimeout = 9000
                    connection.setRequestProperty("Accept", "application/json")
                    connection.setRequestProperty("Cache-Control", "no-store")
                    if (!bearer.isNullOrEmpty()) connection.setRequestProperty("Authorization", "Bearer $bearer")
                    if (body != null) {
                        connection.doOutput = true
                        connection.setRequestProperty("Content-Type", "application/json; charset=utf-8")
                        connection.outputStream.use { it.write(body.toString().toByteArray(Charsets.UTF_8)) }
                    }
                    val status = connection.responseCode
                    val stream = if (status in 200..299) connection.inputStream else connection.errorStream
                    val raw = stream?.bufferedReader(Charsets.UTF_8)?.use { reader ->
                        val limit = 1024 * 1024
                        val buffer = CharArray(8192)
                        val result = StringBuilder()
                        while (result.length <= limit) {
                            val size = reader.read(buffer, 0, minOf(buffer.size, limit + 1 - result.length))
                            if (size < 0) break
                            result.append(buffer, 0, size)
                        }
                        require(result.length <= limit) { "Response too large" }
                        result.toString()
                    } ?: "{}"
                    val parsed = runCatching { JSONObject(raw) }.getOrElse { JSONObject() }
                    if (status !in 200..299) {
                        if (parsed.optString("error").isBlank()) parsed.put("error", "Request rejected (HTTP $status)")
                        parsed.put("_http_status", status)
                    }
                    parsed
                } finally { connection.disconnect() }
            } catch (_: Exception) { JSONObject().put("error", "Server unavailable or endpoint not configured") }
            main.post { onResult(response) }
        }
    }
}
