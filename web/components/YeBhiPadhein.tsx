import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";

export default function YeBhiPadhein({ items }: { items: NewsCard[] }) {
  if (!items.length) return null;
  return (
    <section className="related">
      <h2>ये भी पढ़ें</h2>
      <div className="related-grid">
        {items.map((n, i) => {
          const src = newsImage(n.image);
          return (
            <a key={n.newsid} className="related-item" href={`/news/${n.newsurl}`}>
              <span className="num">{i + 1}</span>
              <h3>{n.title}</h3>
              {src ? <img src={src} alt={n.title} /> : <div className="ph" style={{ width: 96, height: 72, borderRadius: 6 }} />}
            </a>
          );
        })}
      </div>
    </section>
  );
}
