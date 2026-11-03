package asia.aaii.buddhapsychologycare

import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

val Forest = Color(0xFF104535)
val Emerald = Color(0xFF1A7651)
val Gold = Color(0xFFC79A4C)
val Cream = Color(0xFFFBFAF5)
val Leaf = Color(0xFFE7F3E9)

@Composable fun CareTheme(content: @Composable () -> Unit) {
    MaterialTheme(colorScheme = lightColorScheme(primary = Forest, secondary = Emerald,
        tertiary = Gold, background = Cream, surface = Color.White,
        onPrimary = Color.White, onSurface = Forest), content = content)
}
