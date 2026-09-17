import type { Metadata } from "next";
import NewsListItem from "@/components/NewsListItem";
import { getLatest } from "@/lib/queries";
import { listingMeta } from "@/lib/seo";
import { getSiteUrl } from "@/lib/siteUrl";

export async function generateMetadata(): Promise<Metadata> {
  return listingMeta(
    "ताजा खबरें | The Naradmuni",
    "मध्य प्रदेश और छत्तीसगढ़ की ताज़ा हिंदी खबरें।",
    `${getSiteUrl()}/latest`
  );
}

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
