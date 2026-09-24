"use client";

import { useCallback, useEffect, useRef } from "react";

const TOKEN_URL = "/naradmuni/token.php";
const LS_INSTALLED = "nm_pwa_installed";

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

function isStandaloneDisplay(): boolean {
  if (typeof window === "undefined") return false;
  if (window.matchMedia("(display-mode: standalone)").matches) return true;
  if (window.matchMedia("(display-mode: fullscreen)").matches) return true;
  if (window.matchMedia("(display-mode: minimal-ui)").matches) return true;
  const nav = navigator as Navigator & { standalone?: boolean };
  return nav.standalone === true;
}

function markInstalled() {
  try {
    localStorage.setItem(LS_INSTALLED, "1");
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

export default function PwaClient({ iconUrl = "/icons/nm-192.png" }: { iconUrl?: string }) {
  const deferredRef = useRef<BeforeInstallPromptEvent | null>(null);

  const enableNotifications = useCallback(async () => {
    if (typeof window === "undefined") return;
    if (!("Notification" in window) || !("serviceWorker" in navigator)) {
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
        return;
      }

      const messaging = firebase.messaging();
      messaging.useServiceWorker(reg);
      const token = await messaging.getToken();
      if (token) {
        await saveToken(token);
      }

      messaging.onMessage((payload) => {
        const data = payload.data || {};
        const title = data.title || payload.notification?.title || "The Naradmuni";
        const body = data.body || payload.notification?.body || "";
        const n = new Notification(title, {
          body,
          icon: data.icon || iconUrl,
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
    }
  }, [iconUrl]);

  const startInstall = useCallback(async () => {
    if (isStandaloneDisplay()) {
      markInstalled();
      return;
    }
    const ev = deferredRef.current;
    if (!ev) return;
    try {
      await ev.prompt();
      const choice = await ev.userChoice;
      deferredRef.current = null;
      if (choice.outcome === "accepted") {
        markInstalled();
        void enableNotifications();
      }
    } catch {
      /* browser refused the prompt; no card is shown */
    }
  }, [enableNotifications]);

  useEffect(() => {
    if (typeof window === "undefined") return;

    if (isStandaloneDisplay()) {
      markInstalled();
    } else {
      void detectInstalledRelatedApp();
    }

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("/firebase-messaging-sw.js", { scope: "/" })
        .then((reg) => reg.update())
        .catch(() => {});
    }

    if ("Notification" in window && Notification.permission === "granted") {
      void enableNotifications();
    }

    const onBip = (e: Event) => {
      e.preventDefault();
      deferredRef.current = e as BeforeInstallPromptEvent;
    };
    const onInstalled = () => {
      markInstalled();
    };

    window.addEventListener("beforeinstallprompt", onBip);
    window.addEventListener("appinstalled", onInstalled);

    const onOpen = () => void startInstall();
    const onNotif = () => void enableNotifications();
    window.addEventListener("nm:open-install", onOpen);
    window.addEventListener("nm:enable-notifications", onNotif);

    return () => {
      window.removeEventListener("beforeinstallprompt", onBip);
      window.removeEventListener("appinstalled", onInstalled);
      window.removeEventListener("nm:open-install", onOpen);
      window.removeEventListener("nm:enable-notifications", onNotif);
    };
  }, [enableNotifications, startInstall]);

  return null;
}
