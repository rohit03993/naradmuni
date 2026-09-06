import type { NewsCard } from "@/lib/types";
import ArticleActions from "@/components/ArticleActions";
import { newsImage } from "@/lib/images";

type Props = {
  items: NewsCard[];
  shareTitle?: string;
  shareUrl?: string;
};

export default function YeBhiPadhein({ items, shareTitle, shareUrl }: Props) {
  if (!items.length && !(shareTitle && shareUrl)) return null;

  return (
    <section className="related">
      <div className="related-head">
        <h2>ये भी पढ़ें</h2>
        {shareTitle && shareUrl ? <ArticleActions title={shareTitle} url={shareUrl} /> : null}
      </div>
      {items.length ? (
        <div className="related-grid">
          {items.map((n, i) => {
            const src = newsImage(n.image);
            return (
              <a key={n.newsid} className="related-item" href={`/news/${n.newsurl}`}>
                <span className="num">{i + 1}</span>
                <h3>{n.title}</h3>
                {src ? (
                  <img src={src} alt={n.title} />
                ) : (
                  <div className="ph" style={{ width: 96, height: 72, borderRadius: 6 }} />
                )}
              </a>
            );
          })}
        </div>
      ) : null}
    </section>
  );
}
