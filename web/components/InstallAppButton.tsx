"use client";

import { useEffect, useState } from "react";

function alreadyInstalled() {
  try {
    if (localStorage.getItem("nm_pwa_installed") === "1") return true;
  } catch {
    /* ignore */
  }
  if (typeof window === "undefined") return false;
  if (window.matchMedia("(display-mode: standalone)").matches) return true;
  return false;
}

/** Visible Install button for sidebar */
export default function InstallAppButton() {
  const [hidden, setHidden] = useState(true);

  useEffect(() => {
    setHidden(alreadyInstalled());
    const onInstalled = () => setHidden(true);
    window.addEventListener("appinstalled", onInstalled);
    return () => window.removeEventListener("appinstalled", onInstalled);
  }, []);

  if (hidden) return null;

  return (
    <aside className="pwa-promo" aria-label="Install app">
      <img src="/icons/nm-192.png" alt="" width={48} height={48} />
      <div>
        <strong>The Naradmuni App</strong>
        <p>होम स्क्रीन पर इंस्टॉल करें</p>
      </div>
      <button
        type="button"
        className="pwa-install-btn"
        onClick={() => window.dispatchEvent(new Event("nm:open-install"))}
      >
        Install
      </button>
    </aside>
  );
}
