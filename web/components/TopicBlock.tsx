import NewsCardTile from "@/components/NewsCardTile";
import NewsListItem from "@/components/NewsListItem";
import { newsImage } from "@/lib/images";
import type { TopicSection } from "@/lib/queries";

/** Homepage category block: feature + side list + card row (MP style). */
export default function TopicBlock({
  section,
  showEmptyHint = false,
}: {
  section: TopicSection;
  /** Only when this category has zero news in DB — not when dedupe emptied it */
  showEmptyHint?: boolean;
}) {
  const { cat, items, districts } = section;
  const feature = items[0];
  const side = items.slice(1, 4);
  const more = items.slice(4, 8);
  const featureSrc = feature ? newsImage(feature.image) : null;

  return (
    <section className="topic-block">
      <div className="section-head">
        <h2>{cat.hindi_name}</h2>
        {cat.cat_url ? (
          <a className="more" href={`/category/${cat.cat_url}`}>
            और देखें →
          </a>
        ) : null}
      </div>
      {districts.length ? (
        <div className="pills pills--tabs">
          {districts.slice(0, 12).map((c) =>
            c.cat_url ? (
              <a key={c.id} href={`/category/${c.cat_url}`}>
                {c.hindi_name}
              </a>
            ) : null
          )}
        </div>
      ) : null}

      {feature ? (
        <div className="topic-split">
          <a className="topic-feature" href={`/news/${feature.newsurl}`}>
            {featureSrc ? (
              <img src={featureSrc} alt={feature.title} loading="lazy" />
            ) : (
              <div className="ph topic-feature-ph" />
            )}
            <h3>{feature.title}</h3>
          </a>
          <div className="topic-side">
            {side.map((n) => (
              <NewsListItem key={n.newsid} item={n} />
            ))}
          </div>
        </div>
      ) : showEmptyHint ? (
        <p className="topic-empty">जल्द आ रही हैं खबरें — टीम जल्द अपडेट करेगी।</p>
      ) : null}

      {more.length ? (
        <div className="cards cards--home cards--more">
          {more.map((n) => (
            <NewsCardTile key={n.newsid} item={n} />
          ))}
        </div>
      ) : null}
    </section>
  );
}
