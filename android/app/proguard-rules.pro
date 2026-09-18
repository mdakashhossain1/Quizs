# ============================================================
# Flutter ProGuard Rules
# ============================================================

# Flutter wrapper
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }
-keep class io.flutter.** { *; }
-keep class io.flutter.plugins.** { *; }
-keep class io.flutter.plugin.editing.** { *; }

# ============================================================
# Firebase Core & Analytics
# ============================================================
-keep class com.google.firebase.** { *; }
-keep class com.google.android.gms.** { *; }
-dontwarn com.google.firebase.**
-dontwarn com.google.android.gms.**

# Firebase Auth
-keep class com.google.firebase.auth.** { *; }

# Firebase Firestore
-keep class com.google.firebase.firestore.** { *; }

# Firebase Messaging (FCM / Push Notifications)
-keep class com.google.firebase.messaging.** { *; }

# Firebase Crashlytics
-keep class com.google.firebase.crashlytics.** { *; }
-keepattributes SourceFile,LineNumberTable
-keep public class * extends java.lang.Exception
-keep class com.crashlytics.** { *; }
-dontwarn com.crashlytics.**

# ============================================================
# Google Sign-In
# ============================================================
-keep class com.google.android.gms.auth.** { *; }
-keep class com.google.android.gms.common.** { *; }
-keep class com.google.android.gms.internal.** { *; }

# ============================================================
# Google Mobile Ads (AdMob)
# ============================================================
-keep class com.google.android.gms.ads.** { *; }
-dontwarn com.google.android.gms.ads.**
-keep class com.google.ads.** { *; }

# ============================================================
# Flutter Local Notifications
# ============================================================
-keep class com.dexterous.** { *; }
-dontwarn com.dexterous.**

# ============================================================
# Gson
# ============================================================
-keepattributes Signature
-keepattributes *Annotation*
-dontwarn sun.misc.**
-keep class com.google.gson.** { *; }
-keep class * implements com.google.gson.TypeAdapterFactory
-keep class * implements com.google.gson.JsonSerializer
-keep class * implements com.google.gson.JsonDeserializer

# ============================================================
# OkHttp / Networking
# ============================================================
-dontwarn okhttp3.**
-dontwarn okio.**
-keep class okhttp3.** { *; }
-keep class okio.** { *; }
-keep interface okhttp3.** { *; }

# ============================================================
# AndroidX WorkManager / Room
# (flutter_local_notifications and Firebase Messaging schedule
# background work via WorkManager, whose WorkDatabase is a Room
# database instantiated by reflection on its generated *_Impl
# class name — without these keeps R8 renames/strips it and the
# app crashes on launch with "Failed to create an instance of
# androidx.work.impl.WorkDatabase".)
# ============================================================
-keep class androidx.work.** { *; }
-keep class androidx.room.** { *; }
-keep class androidx.sqlite.** { *; }
-dontwarn androidx.work.**
-dontwarn androidx.room.**

# ============================================================
# Kotlin Coroutines
# ============================================================
-keep class kotlinx.coroutines.** { *; }
-dontwarn kotlinx.coroutines.**

# ============================================================
# Parcelables & Serializable
# ============================================================
-keepnames class * implements android.os.Parcelable {
    public static final ** CREATOR;
}

# ============================================================
# Application classes
# ============================================================
-keep class com.quizs.application.arknox.** { *; }

# ============================================================
# Annotations & Reflection
# ============================================================
-keepattributes *Annotation*
-keepattributes Signature
-keepattributes InnerClasses
-keepattributes EnclosingMethod

# ============================================================
# Suppress common warnings
# ============================================================
-dontwarn org.bouncycastle.**
-dontwarn org.conscrypt.**
-dontwarn org.openjsse.**
-dontwarn javax.annotation.**
-dontwarn javax.inject.**

# ============================================================
# Google Play Core (Flutter deferred components - not used but
# referenced by Flutter engine; suppress to avoid R8 errors)
# ============================================================
-dontwarn com.google.android.play.core.splitcompat.SplitCompatApplication
-dontwarn com.google.android.play.core.splitinstall.SplitInstallException
-dontwarn com.google.android.play.core.splitinstall.SplitInstallManager
-dontwarn com.google.android.play.core.splitinstall.SplitInstallManagerFactory
-dontwarn com.google.android.play.core.splitinstall.SplitInstallRequest$Builder
-dontwarn com.google.android.play.core.splitinstall.SplitInstallRequest
-dontwarn com.google.android.play.core.splitinstall.SplitInstallSessionState
-dontwarn com.google.android.play.core.splitinstall.SplitInstallStateUpdatedListener
-dontwarn com.google.android.play.core.tasks.OnFailureListener
-dontwarn com.google.android.play.core.tasks.OnSuccessListener
-dontwarn com.google.android.play.core.tasks.Task
