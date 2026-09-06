import { notFound } from "next/navigation";
import AuthorBox from "@/components/AuthorBox";
import NewsCardTile from "@/components/NewsCardTile";
import { getNewsByAuthor, getTeam } from "@/lib/queries";

type Props = { params: Promise<{ id: string }> };

export default async function AuthorPage({ params }: Props) {
  const { id } = await params;
  const team = await getTeam(Number(id));
  if (!team) notFound();
  const items = await getNewsByAuthor(team.t_id, 30);

  return (
    <section className="author-page">
      <AuthorBox author={team} />
      {items.length ? (
        <>
          <div className="section-head">
            <h2>सभी खबरें</h2>
          </div>
          <div className="cards cards--home">
            {items.map((n, i) => (
              <NewsCardTile key={n.newsid} item={n} priority={i < 4} />
            ))}
          </div>
        </>
      ) : (
        <p style={{ color: "var(--muted)" }}>इस लेखक की अभी कोई प्रकाशित खबर नहीं है।</p>
      )}
    </section>
  );
}
