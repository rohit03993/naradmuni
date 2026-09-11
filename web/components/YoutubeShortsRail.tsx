"use client";

import { useCallback, useEffect, useId, useState } from "react";
import type { YoutubeShort } from "@/lib/youtube";

export default function YoutubeShortsRail({ items }: { items: YoutubeShort[] }) {
  const [playingId, setPlayingId] = useState<string | null>(null);
  const titleId = useId();
  const active = playingId ? items.find((s) => s.id === playingId) : null;

  const close = useCallback(() => setPlayingId(null), []);
  const open = useCallback((id: string) => setPlayingId(id), []);

  useEffect(() => {
    if (!playingId) return;

    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") close();
    };
    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);

    return () => {
      document.body.style.overflow = prevOverflow;
      window.removeEventListener("keydown", onKey);
    };
  }, [playingId, close]);

  if (!items.length) return null;

  const origin =
    typeof window !== "undefined" ? encodeURIComponent(window.location.origin) : "";
  const embedSrc = active
    ? `https://www.youtube.com/embed/${encodeURIComponent(active.id)}` +
      `?autoplay=1&mute=1&playsinline=1&rel=0&modestbranding=1&enablejsapi=1` +
      (origin ? `&origin=${origin}` : "")
    : "";

  return (
    <section className="shorts-block topic-block" aria-label="YouTube Shorts">
      <div className="section-head">
        <h2>Shorts</h2>
      </div>

      <div className="shorts-rail" role="list">
        {items.map((s, i) => {
          const tone = `shorts-thumb--tone${(i % 5) + 1}`;
          return (
            <div key={s.id} className="shorts-card" role="listitem">
              <button
                type="button"
                className={`shorts-thumb ${!s.thumb ? tone : ""}`}
                aria-label={`Play ${s.title}`}
                onClick={() => open(s.id)}
              >
                {s.thumb ? <img src={s.thumb} alt="" loading="lazy" decoding="async" /> : null}
                <span className="shorts-play" aria-hidden="true">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </span>
              </button>
              <p className="shorts-title">{s.title}</p>
            </div>
          );
        })}
      </div>

      {active ? (
        <div
          className="shorts-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby={titleId}
          onClick={close}
        >
          <div
            className="shorts-modal__panel"
            onClick={(e) => e.stopPropagation()}
          >
            <div className="shorts-modal__bar">
              <p id={titleId} className="shorts-modal__title">
                {active.title}
              </p>
              <button type="button" className="shorts-modal__close" onClick={close} aria-label="Close">
                ✕
              </button>
            </div>
            <div className="shorts-modal__player">
              <iframe
                className="shorts-modal__iframe"
                src={embedSrc}
                title={active.title}
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowFullScreen
                referrerPolicy="strict-origin-when-cross-origin"
              />
            </div>
          </div>
        </div>
      ) : null}
    </section>
  );
}
