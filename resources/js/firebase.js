import { initializeApp, getApps, getApp } from 'firebase/app';
import { getAnalytics, isSupported } from 'firebase/analytics';

/**
 * Dynamic Firebase Configuration Reader
 * Reads from Vite environment variables (VITE_FIREBASE_*) with runtime fallbacks.
 * Changing .env values automatically updates Firebase without modifying code.
 */
export function getFirebaseConfig() {
    return {
        apiKey: import.meta.env.VITE_FIREBASE_API_KEY || window.__FIREBASE_CONFIG__?.apiKey || 'AIzaSyAWwIBl3sKDKoMO97_mw_L9rLpDajqQgsY',
        authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || window.__FIREBASE_CONFIG__?.authDomain || 'filefusion-f7889.firebaseapp.com',
        projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || window.__FIREBASE_CONFIG__?.projectId || 'filefusion-f7889',
        storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || window.__FIREBASE_CONFIG__?.storageBucket || 'filefusion-f7889.firebasestorage.app',
        messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || window.__FIREBASE_CONFIG__?.messagingSenderId || '932828976377',
        appId: import.meta.env.VITE_FIREBASE_APP_ID || window.__FIREBASE_CONFIG__?.appId || '1:932828976377:web:d0a6a3c5a16c95672d7e28',
        measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || window.__FIREBASE_CONFIG__?.measurementId || 'G-QRJ6ZQ8WD8'
    };
}

let firebaseApp = null;
let firebaseAnalytics = null;

try {
    const config = getFirebaseConfig();
    if (config.apiKey) {
        firebaseApp = getApps().length === 0 ? initializeApp(config) : getApp();
        if (typeof window !== 'undefined') {
            isSupported().then(supported => {
                if (supported && firebaseApp) {
                    firebaseAnalytics = getAnalytics(firebaseApp);
                    console.log('[Firebase SDK] Initialized with Project:', config.projectId);
                }
            }).catch(() => {});
        }
    }
} catch (e) {
    console.warn('[Firebase SDK Init]:', e);
}

export const app = firebaseApp;
export const analytics = firebaseAnalytics;
export default firebaseApp;
