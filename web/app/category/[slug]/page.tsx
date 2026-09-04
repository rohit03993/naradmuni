import { notFound } from "next/navigation";
import { newsImage } from "@/lib/images";
import { formatStamp } from "@/lib/html";
import { getCategoryByUrl, getChildCategories, countNewsByCategory, getNewsByCategory } from "@/lib/queries";

type Props = {
  params: Promise<{ slug: string }>;
  searchParams: Promise<{ page?: string }>;
};

export default async function CategoryPage({ params, searchParams }: Props) {
  const { slug } = await params;
  const sp = await searchParams;
  const page = Math.max(1, Number(sp.page || 1));
  const cat = await getCategoryByUrl(slug);
  if (!cat) notFound();

  const [items, total, children] = await Promise.all([
    getNewsByCategory(cat.id, page, 21),
    countNewsByCategory(cat.id),
    getChildCategories(cat.id),
  ]);
  const perPage = 21;
  const pages = Math.max(1, Math.ceil(total / perPage));
  const canonical = cat.cat_url || slug;

  return (
    <section>
      <h1 className="cat-h1">{cat.hindi_name}</h1>
      {cat.metad ? <p className="cat-intro">{cat.metad}</p> : null}
      {children.length ? (
        <div className="district">
          <details>
            <summary style={{ cursor: "pointer", fontWeight: 600, marginBottom: 8 }}>जिला चुनें</summary>
            <div className="pills">
              {children.map((c) =>
                c.cat_url ? (
                  <a key={c.id} href={`/category/${c.cat_url}`}>
                    {c.hindi_name}
                  </a>
                ) : null
              )}
            </div>
          </details>
        </div>
      ) : null}
      {items.length ? (
        <div className="cat-tiles">
          {items.map((n) => {
            const src = newsImage(n.image);
            return (
              <a className="cat-tile" key={n.newsid} href={`/news/${n.newsurl}`}>
                {src ? <img src={src} alt={n.title} /> : <div className="ph" style={{ height: 180 }} />}
                <h3>{n.title}</h3>
                <div className="card-meta">{formatStamp(n.date, n.time)}</div>
              </a>
            );
          })}
        </div>
      ) : (
        <p style={{ color: "var(--muted)" }}>इस श्रेणी में अभी कोई प्रकाशित समाचार नहीं है।</p>
      )}
      <div className="pager">
        {page > 1 ? <a href={`/category/${canonical}?page=${page - 1}`}>पिछला</a> : null}
        {page < pages ? <a href={`/category/${canonical}?page=${page + 1}`}>अगला</a> : null}
      </div>
    </section>
  );
}
