/**
 * Initialize PWA service worker registration, update handling, and install UI.
 */
export function initPwa() {
    registerServiceWorker();
    initInstallButtons();
}

let waitingWorker = null;

function registerServiceWorker() {
    if (!('serviceWorker' in navigator) || !window.isSecureContext) {
        return;
    }

    let isRefreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (isRefreshing) return;
        isRefreshing = true;
        window.location.reload();
    });

    window.applyPwaUpdate = () => {
        if (waitingWorker) {
            waitingWorker.postMessage({ type: 'SKIP_WAITING' });
        }
    };

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                if (registration.waiting) {
                    waitingWorker = registration.waiting;
                    window.dispatchEvent(new CustomEvent('pwa-update-available'));
                }

                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            waitingWorker = newWorker;
                            window.dispatchEvent(new CustomEvent('pwa-update-available'));
                        }
                    });
                });
            })
            .catch((err) => {
                console.debug('PWA ServiceWorker registration notice:', err);
            });
    });
}

function initInstallButtons() {
    const getButtons = () => Array.from(document.querySelectorAll('[data-pwa-install]'));
    const statusEl = document.getElementById('pwa-install-status');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    if (isStandalone) {
        return;
    }

    const supportsWebInstall = typeof Navigator.prototype.install === 'function';
    let deferredInstallPrompt = null;

    const setButtonsVisible = (visible) => {
        getButtons().forEach((btn) => {
            btn.hidden = !visible;
            btn.classList.toggle('hidden', !visible);
            btn.classList.toggle('inline-flex', visible);
        });
    };

    const setButtonsDisabled = (disabled) => {
        getButtons().forEach((btn) => {
            if (disabled) {
                btn.setAttribute('aria-disabled', 'true');
            } else {
                btn.removeAttribute('aria-disabled');
            }
        });
    };

    const reportStatus = (message, toastType = null) => {
        if (statusEl) {
            statusEl.textContent = message;
        }
        if (toastType && typeof window.showToast === 'function') {
            window.showToast(message, toastType);
        }
    };

    if (supportsWebInstall) {
        setButtonsVisible(true);
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;

        // Prefer navigator.install() when both mechanisms are available.
        if (!supportsWebInstall) {
            setButtonsVisible(true);
        }
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        setButtonsVisible(false);
        reportStatus('Aplikasi berhasil dipasang di perangkat.', 'success');
    });

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-pwa-install]');
        if (!button || button.getAttribute('aria-disabled') === 'true') {
            return;
        }

        // Use aria-disabled instead of disabled so activating the button does not drop focus.
        setButtonsDisabled(true);

        try {
            if (supportsWebInstall) {
                // Call directly inside click handler to preserve transient user activation.
                await navigator.install();
                reportStatus('Aplikasi berhasil dipasang atau dibuka.', 'success');
                setButtonsVisible(false);
            } else if (deferredInstallPrompt) {
                const promptEvent = deferredInstallPrompt;
                deferredInstallPrompt = undefined;
                await promptEvent.prompt();
                const { outcome } = await promptEvent.userChoice;

                if (outcome === 'accepted') {
                    reportStatus('Aplikasi sedang dipasang ke layar utama.', 'success');
                } else {
                    reportStatus('Pemasangan aplikasi dibatalkan.');
                }

                if (!deferredInstallPrompt) {
                    setButtonsVisible(false);
                }
            }
        } catch (error) {
            if (error?.name === 'AbortError') {
                reportStatus('Pemasangan aplikasi dibatalkan.');
            } else {
                reportStatus('Pemasangan aplikasi tidak dapat dimulai.', 'error');
                console.error('PWA install failed:', error);
            }
        } finally {
            setButtonsDisabled(false);
        }
    });
}
