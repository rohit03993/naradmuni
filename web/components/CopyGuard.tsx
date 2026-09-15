"use client";

import { useEffect } from "react";

function isEditable(target: EventTarget | null) {
  if (!(target instanceof HTMLElement)) return false;
  const tag = target.tagName;
  if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT") return true;
  return target.isContentEditable;
}

function clearSelection() {
  const sel = window.getSelection();
  if (sel && sel.rangeCount) sel.removeAllRanges();
}

/** Discourage casual copy on the public site only. Does not touch admin. */
export default function CopyGuard() {
  useEffect(() => {
    const block = (e: Event) => {
      if (isEditable(e.target)) return;
      e.preventDefault();
      clearSelection();
    };
    const onKey = (e: KeyboardEvent) => {
      if (isEditable(e.target)) return;
      if (!(e.ctrlKey || e.metaKey)) return;
      const key = e.key.toLowerCase();
      if (key === "c" || key === "x" || key === "a" || key === "u") {
        e.preventDefault();
        if (key !== "a") clearSelection();
      }
    };
    const opts: AddEventListenerOptions = { capture: true };
    document.addEventListener("copy", block, opts);
    document.addEventListener("cut", block, opts);
    document.addEventListener("selectstart", block, opts);
    document.addEventListener("contextmenu", block, opts);
    document.addEventListener("dragstart", block, opts);
    document.addEventListener("keydown", onKey, opts);
    return () => {
      document.removeEventListener("copy", block, opts);
      document.removeEventListener("cut", block, opts);
      document.removeEventListener("selectstart", block, opts);
      document.removeEventListener("contextmenu", block, opts);
      document.removeEventListener("dragstart", block, opts);
      document.removeEventListener("keydown", onKey, opts);
    };
  }, []);
  return null;
}
