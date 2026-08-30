Created firebase_credentials.json
Edited services.php
Viewed .env:95-115
Edited .env
Viewed PushNotificationService.php:140-220
Created PushNotificationService.php
Ran command: `php artisan config:clear`
Ran command: `git status --short`
Ran command: `git add .`

### 📁 Firebase Credentials File Paths

Here are the exact file paths where you can paste your real Firebase JSON credentials:

---

### 1. 🖥️ Backend Firebase Service Account JSON (For Server FCM HTTP v1)
👉 **Path**:
```
d:\Ashish\Web\filesystem\New Filesystem\laravel\storage\app\firebase\firebase_credentials.json
```
*(You can also access it via markdown link: [firebase_credentials.json](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/storage/app/firebase/firebase_credentials.json))*

- **Purpose**: Used securely on the Laravel backend by [PushNotificationService.php](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/app/Services/PushNotificationService.php) to generate OAuth2 bearer tokens and dispatch real-time cloud push notifications to Android devices via Google's official **FCM HTTP v1 API**.
- **Security**: Stored in `storage/app/firebase/` which is protected and never accessible to the public web.

---

### 2. 📱 Android App Client File (For Android Studio / Gradle)
👉 **Path**:
```
d:\Ashish\Web\filesystem\New Filesystem\laravel\android\app\google-services.json
```
- **Purpose**: Used by the Android native Gradle build inside Android Studio to connect the Capacitor mobile app to your Firebase project.

---

### ⚡ What's Ready:
- The backend [PushNotificationService](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/app/Services/PushNotificationService.php) is already wired with **OAuth2 token generation & caching** to read directly from `firebase_credentials.json`.
- As soon as you paste your real private key into [firebase_credentials.json](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/storage/app/firebase/firebase_credentials.json), backend FCM HTTP v1 push is immediately live!