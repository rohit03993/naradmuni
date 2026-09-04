import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";

export default function NewsListItem({ item }: { item: NewsCard }) {
  const src = newsImage(item.image);
  return (
    <a className="list-item" href={`/news/${item.newsurl}`}>
      <h3>{item.title}</h3>
      {src ? (
        <img src={src} alt={item.title} loading="lazy" decoding="async" />
      ) : (
        <div className="ph list-ph" />
      )}
    </a>
  );
}
