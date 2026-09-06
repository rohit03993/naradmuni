"use client";

import { useCallback, useState } from "react";
import type { YoutubeShort } from "@/lib/youtube";

export default function YoutubeShortsRail({
  items,
  isDemo = false,
}: {
  items: YoutubeShort[];
  isDemo?: boolean;
}) {
  const [playingId, setPlayingId] = useState<string | null>(null);

  const play = useCallback((id: string) => {
    setPlayingId((prev) => (prev === id ? null : id));
  }, []);

  if (!items.length) return null;

  return (
    <section className="shorts-block topic-block" aria-label="YouTube Shorts">
      <div className="section-head">
        <h2>शॉर्ट्स</h2>
      </div>

      <div className="shorts-rail" role="list">
        {items.map((s, i) => {
          const active = playingId === s.id;
          const tone = `shorts-thumb--tone${(i % 5) + 1}`;
          const embedSrc =
            `https://www.youtube.com/embed/${encodeURIComponent(s.id)}` +
            `?autoplay=1&mute=0&playsinline=1&rel=0&modestbranding=1`;

          return (
            <div key={s.id} className={`shorts-card${active ? " is-playing" : ""}`} role="listitem">
              <div className={`shorts-thumb ${!s.thumb ? tone : ""}`}>
                {active ? (
                  <iframe
                    className="shorts-iframe"
                    src={embedSrc}
                    title={s.title}
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowFullScreen
                  />
                ) : (
                  <>
                    {s.thumb ? <img src={s.thumb} alt="" loading="lazy" decoding="async" /> : null}
                    <button
                      type="button"
                      className="shorts-play"
                      aria-label={`Play ${s.title}`}
                      onClick={() => play(s.id)}
                    >
                      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M8 5v14l11-7z" />
                      </svg>
                    </button>
                  </>
                )}
                {active ? (
                  <button type="button" className="shorts-stop" onClick={() => setPlayingId(null)} aria-label="Stop">
                    ✕
                  </button>
                ) : null}
              </div>
              <p className="shorts-title">{s.title}</p>
            </div>
          );
        })}
      </div>
      {isDemo ? null : null}
    </section>
  );
}
