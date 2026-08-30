package io.filefusion.app;

import android.Manifest;
import android.app.DownloadManager;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.database.Cursor;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Looper;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.media.AudioAttributes;
import android.media.RingtoneManager;
import android.provider.OpenableColumns;
import android.util.Base64;
import android.webkit.CookieManager;
import android.webkit.DownloadListener;
import android.webkit.URLUtil;
import android.webkit.WebView;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.biometric.BiometricManager;
import androidx.biometric.BiometricPrompt;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import com.getcapacitor.BridgeActivity;
import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.concurrent.Executor;
import org.json.JSONArray;
import org.json.JSONObject;

public class MainActivity extends BridgeActivity {

    private static String pendingMode = null;
    private static String pendingText = null;
    private static String pendingUri = null;
    private static String pendingFilesJson = null;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        createNotificationChannels();
        handleSendIntent(getIntent());
        setupDownloadListenerIfAvailable();
    }

    private void createNotificationChannels() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            try {
                NotificationManager notificationManager = getSystemService(NotificationManager.class);
                if (notificationManager != null) {
                    Uri defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                    AudioAttributes audioAttributes = new AudioAttributes.Builder()
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                            .build();

                    long[] vibrationPattern = new long[]{0, 300, 200, 300};

                    // Channel 1: High-Priority Master Channel (v2)
                    NotificationChannel highPriorityChannel = new NotificationChannel(
                            "filefusion_high_priority_v2",
                            "FileFusion Alerts & Security",
                            NotificationManager.IMPORTANCE_HIGH
                    );
                    highPriorityChannel.setDescription("Audible alerts, task reminders and vault security notifications");
                    highPriorityChannel.enableLights(true);
                    highPriorityChannel.enableVibration(true);
                    highPriorityChannel.setVibrationPattern(vibrationPattern);
                    highPriorityChannel.setLockscreenVisibility(android.app.Notification.VISIBILITY_PUBLIC);
                    highPriorityChannel.setSound(defaultSoundUri, audioAttributes);
                    notificationManager.createNotificationChannel(highPriorityChannel);

                    // Channel 2: Primary FileFusion Channel (High Importance with Sound & Vibrate)
                    NotificationChannel defaultChannel = new NotificationChannel(
                            "filefusion_default_channel",
                            "FileFusion Notifications",
                            NotificationManager.IMPORTANCE_HIGH
                    );
                    defaultChannel.setDescription("High priority alerts, security notifications, and task reminders");
                    defaultChannel.enableLights(true);
                    defaultChannel.enableVibration(true);
                    defaultChannel.setVibrationPattern(vibrationPattern);
                    defaultChannel.setLockscreenVisibility(android.app.Notification.VISIBILITY_PUBLIC);
                    defaultChannel.setSound(defaultSoundUri, audioAttributes);
                    notificationManager.createNotificationChannel(defaultChannel);

                    // Channel 3: File Transfers & Tasks
                    NotificationChannel transfersChannel = new NotificationChannel(
                            "filefusion_transfers",
                            "File Transfers & Tasks",
                            NotificationManager.IMPORTANCE_HIGH
                    );
                    transfersChannel.setDescription("File upload, download, and task updates");
                    transfersChannel.enableLights(true);
                    transfersChannel.enableVibration(true);
                    transfersChannel.setVibrationPattern(vibrationPattern);
                    transfersChannel.setSound(defaultSoundUri, audioAttributes);
                    notificationManager.createNotificationChannel(transfersChannel);

                    // Channel 4: Security Alerts
                    NotificationChannel securityChannel = new NotificationChannel(
                            "filefusion_security",
                            "Security & Vault Alerts",
                            NotificationManager.IMPORTANCE_HIGH
                    );
                    securityChannel.setDescription("Critical authentication and vault security events");
                    securityChannel.enableLights(true);
                    securityChannel.enableVibration(true);
                    securityChannel.setVibrationPattern(vibrationPattern);
                    securityChannel.setSound(defaultSoundUri, audioAttributes);
                    notificationManager.createNotificationChannel(securityChannel);

                    // Channel 5: FCM Push Default Channel
                    NotificationChannel fcmChannel = new NotificationChannel(
                            "fcm_default_channel",
                            "Firebase Notifications",
                            NotificationManager.IMPORTANCE_HIGH
                    );
                    fcmChannel.setDescription("System push notifications");
                    fcmChannel.enableLights(true);
                    fcmChannel.enableVibration(true);
                    fcmChannel.setVibrationPattern(vibrationPattern);
                    fcmChannel.setSound(defaultSoundUri, audioAttributes);
                    notificationManager.createNotificationChannel(fcmChannel);
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }

    @Override
    public void onStart() {
        super.onStart();
        setupDownloadListenerIfAvailable();
    }

    private void setupDownloadListenerIfAvailable() {
        try {
            if (getBridge() != null && getBridge().getWebView() != null) {
                final WebView webView = getBridge().getWebView();
                webView.setDownloadListener(new DownloadListener() {
                    @Override
                    public void onDownloadStart(String url, String userAgent, String contentDisposition, String mimeType, long contentLength) {
                        handleNativeDownload(url, userAgent, contentDisposition, mimeType, null);
                    }
                });

                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public void download(final String url, final String fileName) {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                String userAgent = webView.getSettings().getUserAgentString();
                                handleNativeDownload(url, userAgent, null, null, fileName);
                            }
                        });
                    }
                }, "FileFusionAndroidDownload");

                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public String checkBiometricStatus() {
                        try {
                            BiometricManager biometricManager = BiometricManager.from(MainActivity.this);
                            int canAuth = biometricManager.canAuthenticate(BiometricManager.Authenticators.BIOMETRIC_STRONG | BiometricManager.Authenticators.DEVICE_CREDENTIAL);
                            switch (canAuth) {
                                case BiometricManager.BIOMETRIC_SUCCESS:
                                    return "available";
                                case BiometricManager.BIOMETRIC_ERROR_NO_HARDWARE:
                                    return "no_hardware";
                                case BiometricManager.BIOMETRIC_ERROR_HW_UNAVAILABLE:
                                    return "hw_unavailable";
                                case BiometricManager.BIOMETRIC_ERROR_NONE_ENROLLED:
                                    return "none_enrolled";
                                default:
                                    return "unsupported";
                            }
                        } catch (Exception e) {
                            return "error: " + e.getMessage();
                        }
                    }

                    @android.webkit.JavascriptInterface
                    public void authenticate(final String title, final String subtitle, final String callbackFunction) {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                try {
                                    Executor executor = ContextCompat.getMainExecutor(MainActivity.this);
                                    BiometricPrompt biometricPrompt = new BiometricPrompt(MainActivity.this, executor, new BiometricPrompt.AuthenticationCallback() {
                                        @Override
                                        public void onAuthenticationError(int errorCode, @NonNull CharSequence errString) {
                                            super.onAuthenticationError(errorCode, errString);
                                            executeCallback(callbackFunction, false, errString.toString());
                                        }

                                        @Override
                                        public void onAuthenticationSucceeded(@NonNull BiometricPrompt.AuthenticationResult result) {
                                            super.onAuthenticationSucceeded(result);
                                            executeCallback(callbackFunction, true, "Authentication succeeded");
                                        }

                                        @Override
                                        public void onAuthenticationFailed() {
                                            super.onAuthenticationFailed();
                                        }
                                    });

                                    BiometricPrompt.PromptInfo promptInfo = new BiometricPrompt.PromptInfo.Builder()
                                            .setTitle(title != null && !title.isEmpty() ? title : "Unlock FileFusion Vault")
                                            .setSubtitle(subtitle != null && !subtitle.isEmpty() ? subtitle : "Touch the fingerprint sensor to verify identity")
                                            .setAllowedAuthenticators(BiometricManager.Authenticators.BIOMETRIC_STRONG | BiometricManager.Authenticators.DEVICE_CREDENTIAL)
                                            .build();

                                    biometricPrompt.authenticate(promptInfo);
                                } catch (Exception e) {
                                    executeCallback(callbackFunction, false, e.getMessage());
                                }
                            }
                        });
                    }

                    private void executeCallback(final String callbackFunction, final boolean success, final String message) {
                        if (callbackFunction != null && !callbackFunction.isEmpty()) {
                            final String safeMsg = message != null ? message.replace("'", "\\'").replace("\n", " ") : "";
                            final String js = "if(typeof window['" + callbackFunction + "'] === 'function'){ window['" + callbackFunction + "']({ success: " + success + ", message: '" + safeMsg + "' }); }";
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    webView.evaluateJavascript(js, null);
                                }
                            });
                        }
                    }
                }, "FileFusionBiometrics");

                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public void playNotificationSound() {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                try {
                                    Uri notificationUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
                                    android.media.Ringtone r = RingtoneManager.getRingtone(getApplicationContext(), notificationUri);
                                    if (r != null) {
                                        r.play();
                                    }
                                } catch (Exception e) {
                                    e.printStackTrace();
                                }
                            }
                        });
                    }
                }, "FileFusionSoundBridge");

                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public void vibrate(final String type) {
                        runOnUiThread(new Runnable() {
                            @Override
                            public void run() {
                                try {
                                    android.os.Vibrator vibrator = (android.os.Vibrator) getSystemService(Context.VIBRATOR_SERVICE);
                                    if (vibrator != null && vibrator.hasVibrator()) {
                                        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                                            if ("light".equalsIgnoreCase(type) || "selection".equalsIgnoreCase(type)) {
                                                vibrator.vibrate(android.os.VibrationEffect.createOneShot(20, android.os.VibrationEffect.DEFAULT_AMPLITUDE));
                                            } else if ("medium".equalsIgnoreCase(type)) {
                                                vibrator.vibrate(android.os.VibrationEffect.createOneShot(45, android.os.VibrationEffect.DEFAULT_AMPLITUDE));
                                            } else if ("heavy".equalsIgnoreCase(type) || "error".equalsIgnoreCase(type) || "warning".equalsIgnoreCase(type)) {
                                                long[] timings = new long[]{0, 50, 60, 50};
                                                vibrator.vibrate(android.os.VibrationEffect.createWaveform(timings, -1));
                                            } else {
                                                vibrator.vibrate(android.os.VibrationEffect.createOneShot(30, android.os.VibrationEffect.DEFAULT_AMPLITUDE));
                                            }
                                        } else {
                                            vibrator.vibrate(35);
                                        }
                                    }
                                } catch (Exception e) {
                                    e.printStackTrace();
                                }
                            }
                        });
                    }
                }, "FileFusionAndroidHaptics");

                webView.addJavascriptInterface(new Object() {
                    @android.webkit.JavascriptInterface
                    public boolean copy(final String text) {
                        try {
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    try {
                                        android.content.ClipboardManager clipboard = (android.content.ClipboardManager) getSystemService(Context.CLIPBOARD_SERVICE);
                                        android.content.ClipData clip = android.content.ClipData.newPlainText("FileFusion", text != null ? text : "");
                                        if (clipboard != null) {
                                            clipboard.setPrimaryClip(clip);
                                        }
                                    } catch (Exception e) {
                                        e.printStackTrace();
                                    }
                                }
                            });
                            return true;
                        } catch (Exception e) {
                            return false;
                        }
                    }
                }, "FileFusionAndroidClipboard");
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void checkAndRequestDownloadPermissions() {
        ArrayList<String> permsNeeded = new ArrayList<>();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                permsNeeded.add(Manifest.permission.POST_NOTIFICATIONS);
            }
        } else {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE) != PackageManager.PERMISSION_GRANTED) {
                permsNeeded.add(Manifest.permission.WRITE_EXTERNAL_STORAGE);
            }
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.READ_EXTERNAL_STORAGE) != PackageManager.PERMISSION_GRANTED) {
                permsNeeded.add(Manifest.permission.READ_EXTERNAL_STORAGE);
            }
        }

        if (!permsNeeded.isEmpty()) {
            ActivityCompat.requestPermissions(this, permsNeeded.toArray(new String[0]), 1002);
        }
    }

    private void handleNativeDownload(String url, String userAgent, String contentDisposition, String mimeType, String preferredFileName) {
        checkAndRequestDownloadPermissions();

        try {
            DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
            
            String finalFileName = null;
            if (preferredFileName != null && !preferredFileName.trim().isEmpty() && !preferredFileName.equalsIgnoreCase("download")) {
                finalFileName = preferredFileName.trim();
            } else if (contentDisposition != null && contentDisposition.contains("filename=")) {
                int idx = contentDisposition.indexOf("filename=");
                String namePart = contentDisposition.substring(idx + 9).replaceAll("[\"';]", "").trim();
                if (!namePart.isEmpty()) {
                    finalFileName = namePart;
                }
            }

            if (finalFileName == null || finalFileName.isEmpty()) {
                finalFileName = URLUtil.guessFileName(url, contentDisposition, mimeType != null ? mimeType : "application/octet-stream");
            }

            // Remove any unwanted .bin suffix if the base name already has an extension
            if (finalFileName.endsWith(".bin") && finalFileName.length() > 4) {
                String withoutBin = finalFileName.substring(0, finalFileName.length() - 4);
                if (withoutBin.contains(".")) {
                    finalFileName = withoutBin;
                }
            }

            // Sanitize filename for Android filesystem
            finalFileName = finalFileName.replaceAll("[\\\\/:*?\"<>|]", "_");

            String cookies = CookieManager.getInstance().getCookie(url);
            if (cookies != null) {
                request.addRequestHeader("cookie", cookies);
            }
            if (userAgent != null) {
                request.addRequestHeader("User-Agent", userAgent);
            }

            request.setDescription("Downloading to Downloads/FileFusion...");
            request.setTitle(finalFileName);
            request.allowScanningByMediaScanner();
            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);

            // Ensure destination is Downloads/FileFusion/{fileName}
            try {
                java.io.File downloadsDir = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS);
                java.io.File ffDir = new java.io.File(downloadsDir, "FileFusion");
                if (!ffDir.exists()) {
                    ffDir.mkdirs();
                }
            } catch (Exception ignored) {}

            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, "FileFusion/" + finalFileName);

            DownloadManager dm = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);
            if (dm != null) {
                dm.enqueue(request);
                Toast.makeText(this, "Downloading " + finalFileName + " to Downloads/FileFusion", Toast.LENGTH_LONG).show();
            }
        } catch (Exception ex) {
            Toast.makeText(this, "Download error: " + ex.getMessage(), Toast.LENGTH_LONG).show();
        }
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleSendIntent(intent);
    }

    @Override
    public void onResume() {
        super.onResume();
        if (pendingMode != null) {
            new Handler(Looper.getMainLooper()).postDelayed(new Runnable() {
                @Override
                public void run() {
                    dispatchPendingShareToJs();
                }
            }, 800);
        }
    }

    private void handleSendIntent(Intent intent) {
        if (intent == null) return;
        String action = intent.getAction();
        String type = intent.getType();

        if (Intent.ACTION_SEND.equals(action) && type != null) {
            if ("text/plain".equals(type)) {
                String sharedText = intent.getStringExtra(Intent.EXTRA_TEXT);
                if (sharedText == null) {
                    sharedText = intent.getStringExtra(Intent.EXTRA_SUBJECT);
                }
                if (sharedText != null) {
                    dispatchSharedContentToJs("text", sharedText, null, null);
                }
            } else {
                Uri fileUri = intent.getParcelableExtra(Intent.EXTRA_STREAM);
                if (fileUri != null) {
                    JSONObject fileObj = getFileDataFromUri(fileUri);
                    if (fileObj != null) {
                        JSONArray arr = new JSONArray();
                        arr.put(fileObj);
                        dispatchSharedContentToJs("files", null, fileUri.toString(), arr.toString());
                    }
                }
            }
        } else if (Intent.ACTION_SEND_MULTIPLE.equals(action) && type != null) {
            ArrayList<Uri> imageUris = intent.getParcelableArrayListExtra(Intent.EXTRA_STREAM);
            if (imageUris != null && !imageUris.isEmpty()) {
                JSONArray arr = new JSONArray();
                for (Uri u : imageUris) {
                    JSONObject fileObj = getFileDataFromUri(u);
                    if (fileObj != null) {
                        arr.put(fileObj);
                    }
                }
                if (arr.length() > 0) {
                    dispatchSharedContentToJs("files", null, null, arr.toString());
                }
            }
        }
    }

    private JSONObject getFileDataFromUri(Uri uri) {
        if (uri == null) return null;
        try {
            String fileName = "shared_file";
            String mimeType = getContentResolver().getType(uri);
            if (mimeType == null) mimeType = "application/octet-stream";

            Cursor cursor = getContentResolver().query(uri, null, null, null, null);
            if (cursor != null) {
                int nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME);
                if (nameIndex != -1 && cursor.moveToFirst()) {
                    fileName = cursor.getString(nameIndex);
                }
                cursor.close();
            }

            InputStream is = getContentResolver().openInputStream(uri);
            if (is == null) return null;

            ByteArrayOutputStream buffer = new ByteArrayOutputStream();
            int nRead;
            byte[] data = new byte[16384];
            while ((nRead = is.read(data, 0, data.length)) != -1) {
                buffer.write(data, 0, nRead);
            }
            buffer.flush();
            byte[] fileBytes = buffer.toByteArray();
            is.close();

            String base64 = Base64.encodeToString(fileBytes, Base64.NO_WRAP);

            JSONObject json = new JSONObject();
            json.put("name", fileName);
            json.put("type", mimeType);
            json.put("size", fileBytes.length);
            json.put("base64", base64);
            return json;
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }

    private void dispatchSharedContentToJs(String mode, String text, String uri, String filesJson) {
        pendingMode = mode;
        pendingText = text;
        pendingUri = uri;
        pendingFilesJson = filesJson;
        dispatchPendingShareToJs();
    }

    private void dispatchPendingShareToJs() {
        if (pendingMode == null) return;
        if (this.bridge == null || this.bridge.getWebView() == null) return;

        final String mode = pendingMode;
        final String safeText = pendingText != null ? pendingText.replace("\\", "\\\\").replace("'", "\\'").replace("\n", "\\n").replace("\r", "") : "";
        final String safeUri = pendingUri != null ? pendingUri.replace("\\", "\\\\").replace("'", "\\'") : "";
        final String filesData = pendingFilesJson != null ? pendingFilesJson : "[]";

        // Reset pending fields immediately so they don't re-trigger on subsequent lifecycle events
        pendingMode = null;
        pendingText = null;
        pendingUri = null;
        pendingFilesJson = null;

        this.bridge.getWebView().post(new Runnable() {
            @Override
            public void run() {
                String js = String.format(
                    "(function() { " +
                    "  var shareData = { mode: '%s', text: '%s', uri: '%s', files: %s }; " +
                    "  window.__FILEFUSION_PENDING_SHARE__ = shareData; " +
                    "  try { sessionStorage.setItem('ff_shared_intent', JSON.stringify(shareData)); } catch(e){} " +
                    "  window.dispatchEvent(new CustomEvent('filefusion:android-share', { detail: shareData })); " +
                    "})();",
                    mode, safeText, safeUri, filesData
                );
                bridge.getWebView().evaluateJavascript(js, null);
            }
        });
    }
}
