package asia.aaii.buddhapsychologycare

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent

/** 20-screen offline-first native shell. Real APIs are explicitly distinguished from preview modules. */
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent { CareApplication() }
    }
}
