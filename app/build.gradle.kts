plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
    id("org.jetbrains.kotlin.plugin.compose")
}
val endpoint = (findProperty("careApiBaseUrl") as? String ?: System.getenv("CARE_API_BASE_URL") ?: "https://api.example.invalid/api").trim().trimEnd('/')
require(endpoint.startsWith("https://")) { "API endpoint must use HTTPS" }
android {
    namespace = "asia.aaii.buddhapsychologycare"
    compileSdk = 35
    defaultConfig {
        applicationId = "asia.aaii.buddhapsychologycare"
        minSdk = 26
        targetSdk = 35
        versionCode = 172
        versionName = "1.7.2"
        buildConfigField("String", "API_BASE_URL", "\"${endpoint.replace("\\", "\\\\").replace("\"", "\\\"")}\"")
    }
    buildFeatures { compose = true; buildConfig = true }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions { jvmTarget = "17" }
    packaging { resources { excludes += "/META-INF/{AL2.0,LGPL2.1}" } }
}
dependencies {
    implementation(platform("androidx.compose:compose-bom:2024.10.01"))
    implementation("androidx.activity:activity-compose:1.9.3")
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.foundation:foundation")
    implementation("androidx.compose.ui:ui-tooling-preview")
    debugImplementation("androidx.compose.ui:ui-tooling")
}
