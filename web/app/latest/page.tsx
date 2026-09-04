import NewsListItem from "@/components/NewsListItem";
import { getLatest } from "@/lib/queries";

export default async function LatestPage() {
  const items = await getLatest(40);
  return (
    <section>
      <h1 className="cat-h1">ताजा खबरें</h1>
      {items.map((n) => (
        <NewsListItem key={n.newsid} item={n} />
      ))}
    </section>
  );
}
