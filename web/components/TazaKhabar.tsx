import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";

export default function TazaKhabar({ items }: { items: NewsCard[] }) {
  return (
    <section className="taza">
      <h3>
        ताजा खबरें
        <a className="more" href="/latest">और देखें →</a>
      </h3>
      {items.map((n) => {
        const src = newsImage(n.image);
        return (
          <a key={n.newsid} className="taza-item" href={`/news/${n.newsurl}`}>
            <h4>{n.title}</h4>
            {src ? <img src={src} alt={n.title} /> : <div className="ph" style={{ width: 72, height: 72 }} />}
          </a>
        );
      })}
    </section>
  );
}
