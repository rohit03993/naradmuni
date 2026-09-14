"use client";

import { useEffect } from "react";

function isEditable(target: EventTarget | null) {
  if (!(target instanceof HTMLElement)) return false;
  const tag = target.tagName;
  if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT") return true;
  return target.isContentEditable;
}

/** Discourage casual copy on the public site only. Does not touch admin. */
export default function CopyGuard() {
  useEffect(() => {
    const block = (e: Event) => {
      if (isEditable(e.target)) return;
      e.preventDefault();
    };
    const onKey = (e: KeyboardEvent) => {
      if (isEditable(e.target)) return;
      if (!(e.ctrlKey || e.metaKey)) return;
      const key = e.key.toLowerCase();
      if (key === "c" || key === "x" || key === "a") {
        e.preventDefault();
      }
    };
    document.addEventListener("copy", block);
    document.addEventListener("cut", block);
    document.addEventListener("contextmenu", block);
    document.addEventListener("dragstart", block);
    document.addEventListener("keydown", onKey);
    return () => {
      document.removeEventListener("copy", block);
      document.removeEventListener("cut", block);
      document.removeEventListener("contextmenu", block);
      document.removeEventListener("dragstart", block);
      document.removeEventListener("keydown", onKey);
    };
  }, []);
  return null;
}
