import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";
import { plainTitle } from "@/lib/titleHtml";
import NewsTitle from "@/components/NewsTitle";

export default function NewsListItem({ item }: { item: NewsCard }) {
  const src = newsImage(item.image);
  return (
    <a className="list-item" href={`/news/${item.newsurl}`}>
      <h3><NewsTitle html={item.title} /></h3>
      {src ? (
        <img src={src} alt={plainTitle(item.title)} loading="lazy" decoding="async" />
      ) : (
        <div className="ph list-ph" />
      )}
    </a>
  );
}
