import type { NewsCard } from "@/lib/types";
import { newsImage } from "@/lib/images";

/** MP Breaking–style tile: image on top, title below */
export default function NewsCardTile({
  item,
  compact = false,
  priority = false,
}: {
  item: NewsCard;
  compact?: boolean;
  priority?: boolean;
}) {
  const src = newsImage(item.image);
  return (
    <a className={`card${compact ? " card--compact" : ""}`} href={`/news/${item.newsurl}`}>
      {src ? (
        <img
          src={src}
          alt={item.title}
          loading={priority ? "eager" : "lazy"}
          decoding="async"
          {...(priority ? { fetchPriority: "high" as const } : {})}
        />
      ) : (
        <div className="ph card-ph" />
      )}
      <h3>{item.title}</h3>
    </a>
  );
}
