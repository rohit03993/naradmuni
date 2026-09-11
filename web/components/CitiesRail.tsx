import type { Category } from "@/lib/types";

/** Desktop rail: districts that have published news (city names only). */
export default function CitiesRail({ cities }: { cities: Category[] }) {
  const list = cities.filter((c) => c.cat_url).slice(0, 24);
  if (!list.length) return null;

  return (
    <section className="cities-rail" aria-label="शहर">
      <h3>
        शहर
        <span className="cities-rail-note">जिनमें खबरें हैं</span>
      </h3>
      <ul className="cities-rail-list">
        {list.map((c) => (
          <li key={c.id}>
            <a href={`/category/${c.cat_url}`}>{c.hindi_name}</a>
          </li>
        ))}
      </ul>
    </section>
  );
}
