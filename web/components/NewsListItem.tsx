import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";
import NewsDate from "@/components/NewsDate";

export default function NewsListItem({ item }: { item: NewsCard }) {
  const src = newsImage(item.image);
  return (
    <a className="list-item" href={`/news/${item.newsurl}`}>
      <div className="list-item-text">
        <h3>{item.title}</h3>
        <NewsDate date={item.date} />
      </div>
      {src ? (
        <img src={src} alt={item.title} loading="lazy" decoding="async" />
      ) : (
        <div className="ph list-ph" />
      )}
    </a>
  );
}
