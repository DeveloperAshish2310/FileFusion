package io.filefusion.app;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import com.getcapacitor.BridgeActivity;
import java.util.ArrayList;

public class MainActivity extends BridgeActivity {

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        handleSendIntent(getIntent());
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleSendIntent(intent);
    }

    private void handleSendIntent(Intent intent) {
        if (intent == null) return;
        String action = intent.getAction();
        String type = intent.getType();

        if (Intent.ACTION_SEND.equals(action) && type != null) {
            if ("text/plain".equals(type)) {
                String sharedText = intent.getStringExtra(Intent.EXTRA_TEXT);
                if (sharedText != null) {
                    dispatchSharedContentToJs("text", sharedText, null);
                }
            } else {
                Uri imageUri = intent.getParcelableExtra(Intent.EXTRA_STREAM);
                if (imageUri != null) {
                    dispatchSharedContentToJs("file", null, imageUri.toString());
                }
            }
        } else if (Intent.ACTION_SEND_MULTIPLE.equals(action) && type != null) {
            ArrayList<Uri> imageUris = intent.getParcelableArrayListExtra(Intent.EXTRA_STREAM);
            if (imageUris != null && !imageUris.isEmpty()) {
                dispatchSharedContentToJs("files", null, imageUris.get(0).toString());
            }
        }
    }

    private void dispatchSharedContentToJs(String mode, String text, String uri) {
        if (this.bridge == null || this.bridge.getWebView() == null) return;
        
        final String safeText = text != null ? text.replace("'", "\\'").replace("\n", "\\n") : "";
        final String safeUri = uri != null ? uri.replace("'", "\\'") : "";

        this.bridge.getWebView().post(new Runnable() {
            @Override
            public void run() {
                String js = String.format(
                    "window.dispatchEvent(new CustomEvent('filefusion:android-share', { detail: { mode: '%s', text: '%s', uri: '%s' } }));",
                    mode, safeText, safeUri
                );
                bridge.getWebView().evaluateJavascript(js, null);
            }
        });
    }
}
