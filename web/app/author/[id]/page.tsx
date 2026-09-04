import { notFound } from "next/navigation";
import NewsListItem from "@/components/NewsListItem";
import AuthorBox from "@/components/AuthorBox";
import { getNewsByAuthor, getTeam } from "@/lib/queries";

type Props = { params: Promise<{ id: string }> };

export default async function AuthorPage({ params }: Props) {
  const { id } = await params;
  const team = await getTeam(Number(id));
  if (!team) notFound();
  const items = await getNewsByAuthor(team.t_id, 30);
  return (
    <section>
      <AuthorBox author={team} />
      {items.map((n) => (
        <NewsListItem key={n.newsid} item={n} />
      ))}
    </section>
  );
}
