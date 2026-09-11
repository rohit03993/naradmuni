import NewsCardTile from "@/components/NewsCardTile";
import NewsListItem from "@/components/NewsListItem";
import TopicBlock from "@/components/TopicBlock";
import YoutubeShortsRail from "@/components/YoutubeShortsRail";
import { newsImage } from "@/lib/images";
import { getLatest, getLead, getNaradKahinSection, getTopicSections } from "@/lib/queries";
import { getHomepageShorts } from "@/lib/youtube";

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
      getLatest(20),
      getTopicSections(8),
      getNaradKahinSection(),
      getHomepageShorts(),
    ]);
  } catch (e) {
    err = e instanceof Error ? e.message : String(e);
  }

  const rest = latest.filter((n) => n.newsid !== lead?.newsid);
  const secondaries = rest.slice(0, 5);
  const gridNews = rest.slice(0, 8);
  const leadSrc = newsImage(lead?.image);
  const otherTopics = naradKahin
    ? topics.filter((t) => t.cat.id !== naradKahin.cat.id)
    : topics;

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

      {/* नारद कहिन — pinned directly under Shorts */}
      {naradKahin ? <TopicBlock section={naradKahin} /> : null}

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

      {/* Other category blocks */}
      {otherTopics.map((section) => (
        <TopicBlock key={section.cat.id} section={section} />
      ))}
    </div>
  );
}
