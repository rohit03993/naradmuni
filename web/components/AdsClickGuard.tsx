"use client";

import { useEffect } from "react";

/**
 * AdSense Auto ads leave a hidden full-screen layer that can steal header taps.
 * We only disable pointer-events on that *hidden* ghost layer.
 * When a real vignette is open (#google_vignette / visible overlay), clicks stay
 * enabled so "Close / बंद करें" works on mobile and desktop.
 */
function isVignetteOpen() {
  if (typeof window === "undefined") return false;
  if (location.hash.includes("google_vignette")) return true;
  // Google sometimes marks the document while vignette is showing
  if (document.documentElement.getAttribute("data-google-vignette") != null) return true;
  return false;
}

function setPe(el: Element | null, value: "none" | "auto") {
  if (!(el instanceof HTMLElement)) return;
  el.style.setProperty("pointer-events", value, "important");
}

function restoreFullscreenAdClicks() {
  document.querySelectorAll('iframe[id^="aswift_"]').forEach((node) => {
    const iframe = node as HTMLIFrameElement;
    const style = iframe.getAttribute("style") || "";
    const fullscreen =
      style.includes("100vh") ||
      style.includes("100vw") ||
      (iframe.offsetWidth >= window.innerWidth - 2 && iframe.offsetHeight >= window.innerHeight - 2);
    if (!fullscreen) return;
    setPe(iframe, "auto");
    setPe(iframe.closest('[id^="aswift_"][id$="_host"]') || iframe.parentElement, "auto");
  });
  document.querySelectorAll("ins.adsbygoogle").forEach((node) => {
    const el = node as HTMLElement;
    const style = el.getAttribute("style") || "";
    if (style.includes("2147483647")) setPe(el, "auto");
  });
}

function neutralizeHiddenGhostLayersOnly() {
  if (isVignetteOpen()) {
    restoreFullscreenAdClicks();
    return;
  }

  document.querySelectorAll("ins.adsbygoogle").forEach((node) => {
    const el = node as HTMLElement;
    const attr = el.getAttribute("style") || "";
    if (!attr.includes("2147483647")) return;

    const cs = getComputedStyle(el);
    const hidden =
      cs.display === "none" ||
      cs.visibility === "hidden" ||
      attr.includes("display: none") ||
      attr.includes("display:none");

    // Hidden max-z ghost → block stolen taps. Visible vignette → leave clicks alone.
    setPe(el, hidden ? "none" : "auto");
  });

  document.querySelectorAll('iframe[id^="aswift_"]').forEach((node) => {
    const iframe = node as HTMLIFrameElement;
    const style = iframe.getAttribute("style") || "";
    const fullscreen =
      style.includes("100vh") ||
      style.includes("100vw") ||
      (iframe.offsetWidth >= window.innerWidth - 2 && iframe.offsetHeight >= window.innerHeight - 2);
    if (!fullscreen) return;

    const host = iframe.closest('[id^="aswift_"][id$="_host"]') || iframe.parentElement;
    const ins = document.querySelector('ins.adsbygoogle[style*="2147483647"]') as HTMLElement | null;
    const insHidden =
      !!ins &&
      (getComputedStyle(ins).display === "none" ||
        (ins.getAttribute("style") || "").includes("display: none") ||
        (ins.getAttribute("style") || "").includes("display:none"));

    // Only freeze the iframe when the paired Auto-ad shell is hidden (ghost steal).
    if (insHidden) {
      setPe(iframe, "none");
      setPe(host, "none");
    } else {
      setPe(iframe, "auto");
      setPe(host, "auto");
    }
  });
}

export default function AdsClickGuard() {
  useEffect(() => {
    const run = () => neutralizeHiddenGhostLayersOnly();
    run();

    const mo = new MutationObserver(run);
    mo.observe(document.documentElement, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["style", "data-google-vignette"],
    });

    window.addEventListener("hashchange", run);
    const t = window.setInterval(run, 2500);

    return () => {
      mo.disconnect();
      window.removeEventListener("hashchange", run);
      window.clearInterval(t);
    };
  }, []);

  return null;
}
