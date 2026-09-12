"use client";

import { useEffect, useState } from "react";

function isStandalone(): boolean {
  if (typeof window === "undefined") return false;
  if (window.matchMedia("(display-mode: standalone)").matches) return true;
  const nav = navigator as Navigator & { standalone?: boolean };
  return nav.standalone === true;
}

/** Visible Install banner — always show unless already running as installed app */
export default function InstallAppButton({
  iconUrl = "/icons/nm-192.png",
  storeUrl = "https://onelink.to/kqnpym",
}: {
  iconUrl?: string;
  storeUrl?: string;
}) {
  const [hidden, setHidden] = useState(true);

  useEffect(() => {
    setHidden(isStandalone());
    const onInstalled = () => setHidden(true);
    window.addEventListener("appinstalled", onInstalled);
    return () => window.removeEventListener("appinstalled", onInstalled);
  }, []);

  if (hidden) return null;

  return (
    <aside className="pwa-promo" aria-label="Install app">
      <img src={iconUrl} alt="" width={48} height={48} />
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
      {storeUrl ? (
        <a
          href={storeUrl}
          target="_blank"
          rel="noreferrer"
          className="pwa-install-btn"
          style={{ textDecoration: "none", background: "#111", marginLeft: 4 }}
        >
          Download
        </a>
      ) : null}
    </aside>
  );
}
