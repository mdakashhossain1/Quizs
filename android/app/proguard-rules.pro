# ============================================================
# Optimized ProGuard / R8 Rules for Quizs
# Enables high shrinking, optimization, and obfuscation (>25%)
# ============================================================

# Flutter Core & Plugin Reflection
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }

# Allow obfuscation of app classes while keeping JNI native entry points
-keepclasseswithmembernames class * {
    native <methods>;
}

# ============================================================
# Firebase & Google Play Services
# (Official libraries supply their own consumer-rules.pro AAR rules.
#  Avoid blanket keep rules here so R8 can strip unused classes)
# ============================================================
-dontwarn com.google.firebase.**
-dontwarn com.google.android.gms.**

# Firebase Crashlytics
-keepattributes SourceFile,LineNumberTable
-keep public class * extends java.lang.Exception
-dontwarn com.crashlytics.**

# ============================================================
# IronSource / Unity LevelPlay & Mediated Unity Ads
# ============================================================
-keep class com.ironsource.** { *; }
-keep class com.ironsource.adapters.** { *; }
-dontwarn com.ironsource.**
-keep class com.unity3d.ads.** { *; }
-keep class com.unity3d.services.** { *; }
-dontwarn com.unity3d.ads.**
-dontwarn com.unity3d.services.**
-dontwarn com.unity3d.ads-mediation.**

# ============================================================
# Flutter Local Notifications & WorkManager / Room
# (WorkManager's WorkDatabase is instantiated by reflection on
#  its generated *_Impl class name)
# ============================================================
-keep class com.dexterous.** { *; }
-dontwarn com.dexterous.**
-keep class androidx.work.impl.WorkDatabase_Impl { *; }
-keep class * extends androidx.room.RoomDatabase
-dontwarn androidx.work.**
-dontwarn androidx.room.**
-dontwarn androidx.sqlite.**

# ============================================================
# Networking & Third Party libraries (OkHttp, Okio)
# ============================================================
-dontwarn okhttp3.**
-dontwarn okio.**
-dontwarn kotlinx.coroutines.**
-dontwarn sun.misc.**

# ============================================================
# Parcelables & Serializable
# ============================================================
-keepclassmembers class * implements android.os.Parcelable {
    public static final ** CREATOR;
}

# ============================================================
# Attributes & Reflection
# ============================================================
-keepattributes *Annotation*
-keepattributes Signature
-keepattributes InnerClasses
-keepattributes EnclosingMethod

# ============================================================
# Suppress common third-party warnings
# ============================================================
-dontwarn org.bouncycastle.**
-dontwarn org.conscrypt.**
-dontwarn org.openjsse.**
-dontwarn javax.annotation.**
-dontwarn javax.inject.**
-dontwarn com.google.android.play.core.**
