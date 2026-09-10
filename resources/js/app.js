const urlBase64ToUint8Array = (base64) => {
    const padding = '='.repeat((4 - (base64.length % 4)) % 4);
    const base64url = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');

    return Uint8Array.from(atob(base64url), (char) => char.charCodeAt(0));
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

const persistPushSubscription = async () => {
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        return false;
    }

    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidKey) {
        console.warn('Chave VAPID pública não configurada.');
        return false;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey),
    });

    const { endpoint, keys } = subscription.toJSON();

    const response = await fetch('/push-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            endpoint,
            public_key: keys.p256dh,
            auth_token: keys.auth,
            content_encoding: 'aes128gcm',
        }),
    });

    if (!response.ok) {
        throw new Error('Falha ao salvar a assinatura push.');
    }

    return true;
};

window.enablePushNotifications = async () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        console.warn('Web Push não é suportado neste navegador.');
        return;
    }

    try {
        const enabled = await persistPushSubscription();
        if (enabled) {
            window.Livewire.dispatch('push-subscription-saved');
        }
    } catch (error) {
        console.error(error);
    }
};

const registerServiceWorker = async () => {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    try {
        await navigator.serviceWorker.register('/sw.js');
    } catch (error) {
        console.error('Falha ao registrar o service worker.', error);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerServiceWorker);
} else {
    registerServiceWorker();
}