import type { YoutubeShort } from "@/lib/youtube";

export default function YoutubeShortsRail({ items }: { items: YoutubeShort[] }) {
  if (!items.length) return null;

  return (
    <section className="shorts-block topic-block" aria-label="YouTube Shorts">
      <div className="section-head">
        <h2>शॉर्ट्स</h2>
        <a
          className="more"
          href="https://www.youtube.com/shorts"
          target="_blank"
          rel="noreferrer"
        >
          YouTube →
        </a>
      </div>
      <div className="shorts-rail">
        {items.map((s) => (
          <a
            key={s.id}
            className="shorts-card"
            href={s.url}
            target="_blank"
            rel="noreferrer"
            title={s.title}
          >
            <span className="shorts-thumb">
              {s.thumb ? <img src={s.thumb} alt="" loading="lazy" decoding="async" /> : <span className="ph shorts-ph" />}
              <span className="shorts-play" aria-hidden="true">
                ▶
              </span>
            </span>
            <span className="shorts-title">{s.title}</span>
          </a>
        ))}
      </div>
    </section>
  );
}
