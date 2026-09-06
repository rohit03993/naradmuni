import type { YoutubeShort } from "@/lib/youtube";

export default function YoutubeShortsRail({
  items,
  isDemo = false,
}: {
  items: YoutubeShort[];
  isDemo?: boolean;
}) {
  if (!items.length) return null;

  return (
    <section className="shorts-block topic-block" aria-label="YouTube Shorts">
      <div className="section-head">
        <h2>
          शॉर्ट्स
          {isDemo ? <span className="shorts-demo-badge">डेमो</span> : null}
        </h2>
        {isDemo ? (
          <span className="more shorts-demo-note">Admin में API जोड़ें</span>
        ) : (
          <a className="more" href="https://www.youtube.com/shorts" target="_blank" rel="noreferrer">
            YouTube →
          </a>
        )}
      </div>
      <div className="shorts-rail">
        {items.map((s, i) => {
          const tone = `shorts-thumb--tone${(i % 5) + 1}`;
          const inner = (
            <>
              <span className={`shorts-thumb ${tone}`}>
                {s.thumb ? <img src={s.thumb} alt="" loading="lazy" decoding="async" /> : null}
                <span className="shorts-play" aria-hidden="true">
                  ▶
                </span>
              </span>
              <span className="shorts-title">{s.title}</span>
            </>
          );

          if (isDemo) {
            return (
              <div key={s.id} className="shorts-card shorts-card--demo" title={s.title}>
                {inner}
              </div>
            );
          }

          return (
            <a
              key={s.id}
              className="shorts-card"
              href={s.url}
              target="_blank"
              rel="noreferrer"
              title={s.title}
            >
              {inner}
            </a>
          );
        })}
      </div>
    </section>
  );
}
