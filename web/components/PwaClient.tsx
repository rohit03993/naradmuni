"use client";

import { useCallback, useEffect, useRef, useState } from "react";

const TOKEN_URL = "/naradmuni/token.php";
const LS_INSTALLED = "nm_pwa_installed";
const LS_DISMISS_UNTIL = "nm_pwa_dismiss_until";
const SS_SESSION_DISMISS = "nm_pwa_session_dismiss";
const SHOW_DELAY_MS = 45_000; // don't fight first paint / menu taps
const REDISPLAY_AFTER_MS = 24 * 60 * 60 * 1000;

type BeforeInstallPromptEvent = Event & {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: "accepted" | "dismissed" }>;
};

type FirebaseMessaging = {
  useServiceWorker: (reg: ServiceWorkerRegistration) => void;
  getToken: () => Promise<string>;
  onMessage: (cb: (payload: {
    data?: Record<string, string>;
    notification?: { title?: string; body?: string };
  }) => void) => void;
};

function loadScript(src: string) {
  return new Promise<void>((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) {
      resolve();
      return;
    }
    const s = document.createElement("script");
    s.src = src;
    s.async = true;
    s.onload = () => resolve();
    s.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.head.appendChild(s);
  });
}

async function saveToken(token: string) {
  const body = new URLSearchParams({ token });
  await fetch(TOKEN_URL, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body,
  });
  localStorage.setItem("nm_fcm_token_saved", token);
}

function isPwaInstalled(): boolean {
  if (typeof window === "undefined") return false;
  try {
    if (localStorage.getItem(LS_INSTALLED) === "1") return true;
  } catch {
    /* ignore */
  }
  if (window.matchMedia("(display-mode: standalone)").matches) return true;
  if (window.matchMedia("(display-mode: fullscreen)").matches) return true;
  if (window.matchMedia("(display-mode: minimal-ui)").matches) return true;
  const nav = navigator as Navigator & { standalone?: boolean };
  return nav.standalone === true;
}

function markInstalled() {
  try {
    localStorage.setItem(LS_INSTALLED, "1");
    localStorage.removeItem(LS_DISMISS_UNTIL);
    sessionStorage.removeItem(SS_SESSION_DISMISS);
  } catch {
    /* ignore */
  }
}

async function detectInstalledRelatedApp(): Promise<boolean> {
  try {
    const nav = navigator as Navigator & {
      getInstalledRelatedApps?: () => Promise<Array<{ platform?: string }>>;
    };
    if (typeof nav.getInstalledRelatedApps !== "function") return false;
    const apps = await nav.getInstalledRelatedApps();
    if (apps?.length) {
      markInstalled();
      return true;
    }
  } catch {
    /* ignore */
  }
  return false;
}

async function shouldAutoShowInstall(): Promise<boolean> {
  if (isPwaInstalled()) {
    markInstalled();
    return false;
  }
  if (await detectInstalledRelatedApp()) return false;
  try {
    if (sessionStorage.getItem(SS_SESSION_DISMISS) === "1") return false;
    const until = Number(localStorage.getItem(LS_DISMISS_UNTIL) || "0");
    if (until && Date.now() < until) return false;
  } catch {
    /* allow */
  }
  return true;
}

function dismissInstallPrompt() {
  try {
    sessionStorage.setItem(SS_SESSION_DISMISS, "1");
    localStorage.setItem(LS_DISMISS_UNTIL, String(Date.now() + REDISPLAY_AFTER_MS));
  } catch {
    /* ignore */
  }
}

export function openNaradmuniInstall() {
  if (typeof window !== "undefined") {
    window.dispatchEvent(new Event("nm:open-install"));
  }
}

export function requestNaradmuniNotifications() {
  if (typeof window !== "undefined") {
    window.dispatchEvent(new Event("nm:enable-notifications"));
  }
}

export default function PwaClient() {
  const deferredRef = useRef<BeforeInstallPromptEvent | null>(null);
  const [installed, setInstalled] = useState(false);
  const [showModal, setShowModal] = useState(false);
  const [iosTip, setIosTip] = useState(false);
  const [notifState, setNotifState] = useState<"idle" | "on" | "denied" | "unsupported">("idle");

  const enableNotifications = useCallback(async () => {
    if (typeof window === "undefined") return;
    if (!("Notification" in window) || !("serviceWorker" in navigator)) {
      setNotifState("unsupported");
      return;
    }

    try {
      const reg = await navigator.serviceWorker.register("/firebase-messaging-sw.js", { scope: "/" });
      await navigator.serviceWorker.ready;

      await loadScript("https://www.gstatic.com/firebasejs/8.10.2/firebase-app.js");
      await loadScript("https://www.gstatic.com/firebasejs/8.10.2/firebase-messaging.js");

      const firebase = (
        window as unknown as {
          firebase: {
            apps: unknown[];
            initializeApp: (c: Record<string, string>) => void;
            messaging: () => FirebaseMessaging;
          };
        }
      ).firebase;

      if (!firebase.apps.length) {
        firebase.initializeApp({
          apiKey: "AIzaSyDfbS00KErQAwcFwhP6Iiey0lAJPO72lnU",
          authDomain: "the-naradmuni.firebaseapp.com",
          databaseURL: "https://the-naradmuni-default-rtdb.firebaseio.com",
          projectId: "the-naradmuni",
          storageBucket: "the-naradmuni.appspot.com",
          messagingSenderId: "678566510077",
        });
      }

      const permission = await Notification.requestPermission();
      if (permission !== "granted") {
        setNotifState("denied");
        return;
      }

      const messaging = firebase.messaging();
      messaging.useServiceWorker(reg);
      const token = await messaging.getToken();
      if (token) {
        await saveToken(token);
        setNotifState("on");
      }

      messaging.onMessage((payload) => {
        const data = payload.data || {};
        const title = data.title || payload.notification?.title || "The Naradmuni";
        const body = data.body || payload.notification?.body || "";
        const n = new Notification(title, {
          body,
          icon: data.icon || "/icons/nm-192.png",
          // @ts-expect-error Chromium image
          image: data.image,
        });
        n.onclick = () => {
          window.focus();
          window.location.href = data.click_action || "/";
          n.close();
        };
      });
    } catch (err) {
      console.warn("Naradmuni notifications:", err);
      setNotifState("denied");
    }
  }, []);

  const openInstallModal = useCallback(() => {
    if (isPwaInstalled()) {
      setInstalled(true);
      return;
    }
    setIosTip(false);
    setShowModal(true);
  }, []);

  useEffect(() => {
    if (typeof window === "undefined") return;

    const syncInstalled = async () => {
      if (isPwaInstalled() || (await detectInstalledRelatedApp())) {
        markInstalled();
        setInstalled(true);
      }
    };
    void syncInstalled();

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("/firebase-messaging-sw.js", { scope: "/" })
        .then((reg) => reg.update())
        .catch(() => {});
    }

    if ("Notification" in window && Notification.permission === "granted") {
      setNotifState("on");
      void enableNotifications();
    } else if ("Notification" in window && Notification.permission === "denied") {
      setNotifState("denied");
    }

    const onBip = (e: Event) => {
      e.preventDefault();
      deferredRef.current = e as BeforeInstallPromptEvent;
    };
    const onInstalled = () => {
      markInstalled();
      setInstalled(true);
      setShowModal(false);
      setIosTip(false);
    };

    window.addEventListener("beforeinstallprompt", onBip);
    window.addEventListener("appinstalled", onInstalled);

    const onOpen = () => openInstallModal();
    const onNotif = () => void enableNotifications();
    window.addEventListener("nm:open-install", onOpen);
    window.addEventListener("nm:enable-notifications", onNotif);

    const timer = window.setTimeout(() => {
      void shouldAutoShowInstall().then((ok) => {
        if (ok && !isPwaInstalled()) setShowModal(true);
      });
    }, SHOW_DELAY_MS);

    return () => {
      window.clearTimeout(timer);
      window.removeEventListener("beforeinstallprompt", onBip);
      window.removeEventListener("appinstalled", onInstalled);
      window.removeEventListener("nm:open-install", onOpen);
      window.removeEventListener("nm:enable-notifications", onNotif);
    };
  }, [enableNotifications, openInstallModal]);

  const isIos =
    typeof navigator !== "undefined" &&
    /iphone|ipad|ipod/i.test(navigator.userAgent) &&
    !(navigator as Navigator & { standalone?: boolean }).standalone;

  const onInstall = async () => {
    const ev = deferredRef.current;
    if (ev) {
      await ev.prompt();
      const choice = await ev.userChoice;
      deferredRef.current = null;
      if (choice.outcome === "accepted") {
        markInstalled();
        setInstalled(true);
        setShowModal(false);
        void enableNotifications();
      } else {
        dismissInstallPrompt();
        setShowModal(false);
      }
      return;
    }
    setIosTip(true);
  };

  const dismissModal = () => {
    dismissInstallPrompt();
    setShowModal(false);
    setIosTip(false);
  };

  return (
    <>
      {showModal ? (
        <div className="pwa-overlay" role="dialog" aria-modal="true" aria-label="Install Naradmuni app">
          <div className="pwa-card">
            <button type="button" className="pwa-card-close" onClick={dismissModal} aria-label="बाद में">
              ✕
            </button>
            <img className="pwa-card-icon" src="/icons/nm-192.png" alt="" width={64} height={64} />
            <h3>The Naradmuni ऐप इंस्टॉल करें</h3>
            <p>होम स्क्रीन पर रखें — तेज़ खुलता है, और नई खबर आने पर सूचना मिलती है।</p>

            {iosTip ? (
              <div className="pwa-ios-tip">
                {isIos ? (
                  <p>
                    <strong>Safari:</strong> Share (□↑) → <strong>Add to Home Screen</strong>
                  </p>
                ) : (
                  <p>
                    Chrome मेनू (⋮) → <strong>Install app</strong> / <strong>Add to Home screen</strong>
                    <br />
                    <small>Note: Incognito में Install अक्सर काम नहीं करता — सामान्य Chrome विंडो खोलें।</small>
                  </p>
                )}
                <button type="button" className="pwa-install-btn pwa-install-btn--block" onClick={dismissModal}>
                  समझ गया
                </button>
              </div>
            ) : (
              <div className="pwa-card-actions">
                <button type="button" className="pwa-install-btn pwa-install-btn--block" onClick={() => void onInstall()}>
                  अभी इंस्टॉल करें
                </button>
                <button type="button" className="pwa-later-btn" onClick={dismissModal}>
                  बाद में
                </button>
              </div>
            )}
          </div>
        </div>
      ) : null}
    </>
  );
}
