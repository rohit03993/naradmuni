import NewsCardTile from "@/components/NewsCardTile";
import NewsListItem from "@/components/NewsListItem";
import TopicBlock from "@/components/TopicBlock";
import YoutubeShortsRail from "@/components/YoutubeShortsRail";
import { newsImage } from "@/lib/images";
import {
  getLatest,
  getLead,
  getNaradKahinSection,
  getTopicSections,
} from "@/lib/queries";
import type { NewsCard } from "@/lib/types";
import { getHomepageShorts } from "@/lib/youtube";

/** Keep first N cards not already shown elsewhere on the homepage. */
function takeUnique(pool: NewsCard[], seen: Set<number>, limit: number): NewsCard[] {
  const out: NewsCard[] = [];
  for (const item of pool) {
    const id = Number(item.newsid);
    if (!id || seen.has(id)) continue;
    seen.add(id);
    out.push(item);
    if (out.length >= limit) break;
  }
  return out;
}

function dedupeSection<T extends { items: NewsCard[] }>(section: T, seen: Set<number>, limit = 8): T {
  return { ...section, items: takeUnique(section.items, seen, limit) };
}

export default async function HomePage() {
  let lead = null;
  let latest: Awaited<ReturnType<typeof getLatest>> = [];
  let topics: Awaited<ReturnType<typeof getTopicSections>> = [];
  let naradKahin: Awaited<ReturnType<typeof getNaradKahinSection>> = null;
  let shorts: Awaited<ReturnType<typeof getHomepageShorts>> = { items: [], isDemo: true };
  let err = "";

  try {
    [lead, latest, topics, naradKahin, shorts] = await Promise.all([
      getLead(),
      getLatest(40),
      getTopicSections(8),
      getNaradKahinSection(),
      getHomepageShorts(),
    ]);
  } catch (e) {
    err = e instanceof Error ? e.message : String(e);
  }

  const seen = new Set<number>();
  if (lead?.newsid) seen.add(Number(lead.newsid));

  const pool = latest.filter((n) => n.newsid != null);

  // Claim नारद कहिन stories BEFORE hero side / ताज़ा so they are not stolen
  const naradFromDb = naradKahin?.items.length ?? 0;
  const naradBlock = naradKahin ? dedupeSection(naradKahin, seen, 8) : null;

  const secondaries = takeUnique(pool, seen, 5);
  const gridNews = takeUnique(pool, seen, 8);

  const otherTopicsRaw = naradKahin
    ? topics.filter((t) => t.cat.id !== naradKahin.cat.id)
    : topics;
  const otherTopics = otherTopicsRaw.map((section) => ({
    section: dedupeSection(section, seen, 8),
    fromDb: section.items.length,
  }));

  const leadSrc = newsImage(lead?.image);

  if (err) {
    return (
      <div style={{ padding: 24, background: "#FEF2F2", borderRadius: 8, border: "1px solid #FECACA" }}>
        <h1 style={{ marginTop: 0 }}>Database connection failed</h1>
        <p>
          Start <strong>MySQL</strong> in XAMPP, then refresh this page.
        </p>
        <pre style={{ whiteSpace: "pre-wrap", fontSize: 13 }}>{err}</pre>
      </div>
    );
  }

  return (
    <div className="home">
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

      {naradBlock ? (
        <TopicBlock section={naradBlock} showEmptyHint={naradFromDb === 0} />
      ) : null}

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

      {otherTopics.map(({ section, fromDb }) => (
        <TopicBlock key={section.cat.id} section={section} showEmptyHint={fromDb === 0} />
      ))}
    </div>
  );
}
