import NewsListItem from "@/components/NewsListItem";
import NewsCardTile from "@/components/NewsCardTile";
import YoutubeShortsRail from "@/components/YoutubeShortsRail";
import { newsImage } from "@/lib/images";
import { getLatest, getLead, getTopicSections } from "@/lib/queries";
import { getHomepageShorts } from "@/lib/youtube";

export default async function HomePage() {
  let lead = null;
  let latest: Awaited<ReturnType<typeof getLatest>> = [];
  let topics: Awaited<ReturnType<typeof getTopicSections>> = [];
  let shorts: Awaited<ReturnType<typeof getHomepageShorts>> = { items: [], isDemo: true };
  let err = "";

  try {
    [lead, latest, topics, shorts] = await Promise.all([
      getLead(),
      getLatest(20),
      getTopicSections(8),
      getHomepageShorts(),
    ]);
  } catch (e) {
    err = e instanceof Error ? e.message : String(e);
  }

  const rest = latest.filter((n) => n.newsid !== lead?.newsid);
  const secondaries = rest.slice(0, 5);
  const gridNews = rest.slice(0, 8);
  const leadSrc = newsImage(lead?.image);

  if (err) {
    return (
      <div style={{ padding: 24, background: "#FEF2F2", borderRadius: 8, border: "1px solid #FECACA" }}>
        <h1 style={{ marginTop: 0 }}>Database connection failed</h1>
        <p>Start <strong>MySQL</strong> in XAMPP, then refresh this page.</p>
        <pre style={{ whiteSpace: "pre-wrap", fontSize: 13 }}>{err}</pre>
      </div>
    );
  }

  return (
    <div className="home">
      {/* Lead — MP style: big photo + compact side stories */}
      <div className="hero">
        {lead ? (
          <a className="hero-lead" href={`/news/${lead.newsurl}`}>
            {leadSrc ? (
              <img src={leadSrc} alt={lead.title} fetchPriority="high" />
            ) : (
              <div className="ph hero-ph" />
            )}
            <h2>{lead.title}</h2>
          </a>
        ) : (
          <p>No published news found.</p>
        )}
        <div className="hero-side">
          {secondaries.map((n) => (
            <NewsListItem key={n.newsid} item={n} />
          ))}
        </div>
      </div>

      <YoutubeShortsRail items={shorts.items} />

      {/* Dense photo grid — like MP “राज्य” / top cards */}
      <section className="topic-block">
        <div className="section-head">
          <h2>ताज़ा समाचार</h2>
          <a className="more" href="/latest">
            और देखें →
          </a>
        </div>
        <div className="cards cards--home">
          {gridNews.map((n, i) => (
            <NewsCardTile key={n.newsid} item={n} priority={i < 2} />
          ))}
        </div>
      </section>

      {/* Category blocks — featured + side list (MP “धर्म” pattern) */}
      {topics.map(({ cat, items, districts }) => {
        const feature = items[0];
        const side = items.slice(1, 4);
        const more = items.slice(4, 8);
        const featureSrc = feature ? newsImage(feature.image) : null;
        return (
          <section key={cat.id} className="topic-block">
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
      })}
    </div>
  );
}
