"use client";

import { useEffect } from "react";

/**
 * AdSense Auto ads sometimes leave a full-viewport iframe host at max z-index
 * that intercepts taps on the sticky header (hamburger). Neutralize those hosts
 * only — in-flow ad units keep normal clicks.
 */
function neutralizeStealLayers() {
  document.querySelectorAll('iframe[id^="aswift_"]').forEach((node) => {
    const iframe = node as HTMLIFrameElement;
    const style = iframe.getAttribute("style") || "";
    const fullscreen =
      style.includes("100vh") ||
      style.includes("100vw") ||
      (iframe.offsetWidth >= window.innerWidth - 2 && iframe.offsetHeight >= window.innerHeight - 2);
    if (!fullscreen) return;
    iframe.style.setProperty("pointer-events", "none", "important");
    const host = iframe.closest('[id^="aswift_"][id$="_host"]') || iframe.parentElement;
    if (host instanceof HTMLElement) {
      host.style.setProperty("pointer-events", "none", "important");
    }
  });
  document.querySelectorAll("ins.adsbygoogle").forEach((node) => {
    const el = node as HTMLElement;
    const style = el.getAttribute("style") || "";
    if (style.includes("2147483647")) {
      el.style.setProperty("pointer-events", "none", "important");
    }
  });
}

export default function AdsClickGuard() {
  useEffect(() => {
    neutralizeStealLayers();
    const mo = new MutationObserver(() => neutralizeStealLayers());
    mo.observe(document.documentElement, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["style"],
    });
    const t = window.setInterval(neutralizeStealLayers, 2000);
    return () => {
      mo.disconnect();
      window.clearInterval(t);
    };
  }, []);

  return null;
}
