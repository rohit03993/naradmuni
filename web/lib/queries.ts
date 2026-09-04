import { query } from "./db";
import { asHtmlString } from "./html";
import type { Ad, Category, NewsArticle, NewsCard, SitePage, Team } from "./types";

const PUB = "Published";

/** State parents that own district/city children (matched case-insensitively on cat_url). */
const STATE_SLUGS = ["madhya-pradesh", "chhattisgarh"];

const CAT_COLS = `id, hindi_name, cat_url, metad, metat, parent, menu, short, latter, main_heading`;
const CARD_COLS = `n.newsid, n.title, n.newsurl, n.image, n.short_description, n.date, n.time, c.hindi_name, c.cat_url`;

const TOP_LEVEL = `(parent IS NULL OR parent = '' OR parent = '0')`;
const NOT_VIDEO = `(n.newstype IS NULL OR n.newstype != 'Video')`;

/** Primary Home Category OR checkbox tags in news_cat. */
function inCategorySql(alias = "n"): string {
  return `(${alias}.category = ? OR EXISTS (
    SELECT 1 FROM news_cat nc
    WHERE nc.news_id = ${alias}.newsid AND nc.category = ?
  ))`;
}

function isFilled(c: Category): boolean {
  return !!(c.cat_url && c.hindi_name);
}

let stateParentIdsCache: { at: number; ids: string[] } | null = null;

export async function getStateParentIds(): Promise<string[]> {
  if (stateParentIdsCache && Date.now() - stateParentIdsCache.at < 60_000) {
    return stateParentIdsCache.ids;
  }
  const placeholders = STATE_SLUGS.map(() => "?").join(",");
  const rows = await query<{ id: number }>(
    `SELECT id FROM categories
     WHERE LOWER(cat_url) IN (${placeholders})
       AND ${TOP_LEVEL}`,
    STATE_SLUGS
  );
  const ids = rows.map((r) => String(r.id));
  stateParentIdsCache = { at: Date.now(), ids };
  return ids;
}

/** Top nav topics (states + Rashifal/Cinema/etc.) — menu Yes, top-level only. */
export async function getNavCategories(): Promise<Category[]> {
  return query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE menu = 'Yes' AND ${TOP_LEVEL}
       AND cat_url IS NOT NULL AND cat_url != ''
       AND hindi_name IS NOT NULL AND hindi_name != ''
     ORDER BY short ASC, id ASC`
  );
}

/** Districts/cities under MP + CG only — never invent cities. */
export async function getDistricts(): Promise<Category[]> {
  const parents = await getStateParentIds();
  if (!parents.length) return [];
  const ph = parents.map(() => "?").join(",");
  const rows = await query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE parent IN (${ph})
       AND cat_url IS NOT NULL AND cat_url != ''
       AND hindi_name IS NOT NULL AND hindi_name != ''
     ORDER BY latter ASC, hindi_name ASC`,
    parents
  );
  return rows.filter(isFilled);
}

/** Featured cities for header pin — main_heading=Yes under MP/CG, else first few districts. */
export async function getFeaturedDistricts(limit = 6): Promise<Category[]> {
  const parents = await getStateParentIds();
  if (!parents.length) return [];
  const ph = parents.map(() => "?").join(",");
  const take = Math.min(Math.max(limit, 1), 12);
  const featured = await query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE parent IN (${ph})
       AND main_heading = 'Yes'
       AND cat_url IS NOT NULL AND cat_url != ''
       AND hindi_name IS NOT NULL AND hindi_name != ''
     ORDER BY short ASC, latter ASC, id ASC
     LIMIT ${take}`,
    parents
  );
  if (featured.length) return featured.filter(isFilled);
  const fallback = await query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE parent IN (${ph})
       AND cat_url IS NOT NULL AND cat_url != ''
       AND hindi_name IS NOT NULL AND hindi_name != ''
     ORDER BY short ASC, latter ASC, id ASC
     LIMIT ${take}`,
    parents
  );
  return fallback.filter(isFilled);
}

export async function getCategoryByUrl(slug: string): Promise<Category | null> {
  if (!slug) return null;
  const rows = await query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE LOWER(cat_url) = LOWER(?)
     LIMIT 1`,
    [slug]
  );
  return rows[0] || null;
}

export async function getChildCategories(parentId: number): Promise<Category[]> {
  const rows = await query<Category>(
    `SELECT ${CAT_COLS}
     FROM categories
     WHERE parent = ?
       AND cat_url IS NOT NULL AND cat_url != ''
       AND hindi_name IS NOT NULL AND hindi_name != ''
     ORDER BY latter ASC, hindi_name ASC`,
    [String(parentId)]
  );
  return rows.filter(isFilled);
}

export async function getArticleBySlug(slug: string): Promise<(NewsArticle & { author?: Team }) | null> {
  const rows = await query<NewsArticle>(
    `SELECT n.newsid, n.title, n.newsurl, n.image, n.short_description, n.description,
            n.date, n.time, n.img_abt, n.status, n.team_id, n.metat, n.metad, n.newstype,
            n.category, c.hindi_name, c.cat_url
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.newsurl = ?
     LIMIT 1`,
    [slug]
  );
  const article = rows[0];
  if (!article) return null;
  // mysql2 may return Buffer for longtext/blob — always normalize to string
  article.description = asHtmlString(article.description);
  if (article.short_description != null) {
    article.short_description = asHtmlString(article.short_description);
  }
  const team = await query<Team>(
    `SELECT t_id, name, email, designation, image, fb_link, tw_link FROM team WHERE t_id = ? LIMIT 1`,
    [article.team_id || 0]
  );
  return { ...article, author: team[0] };
}

export async function getRelated(categoryId: string | number | null | undefined, newsid: number): Promise<NewsCard[]> {
  if (categoryId === undefined || categoryId === null || categoryId === "") {
    return getTaza(4);
  }
  const cat = String(categoryId);
  return query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND ${inCategorySql("n")} AND n.newsid != ?
       AND ${NOT_VIDEO}
     ORDER BY n.newsid DESC
     LIMIT 4`,
    [PUB, cat, cat, newsid]
  );
}

/**
 * ताज़ा list: prefer latest_news='Yes' (by latest_priority), then fill with newest Published.
 */
export async function getTaza(limit = 8): Promise<NewsCard[]> {
  const n = Math.min(Math.max(Number(limit) || 8, 1), 40);
  const preferred = await query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND ${NOT_VIDEO} AND n.latest_news = 'Yes'
     ORDER BY CAST(n.latest_priority AS UNSIGNED) ASC, n.newsid DESC
     LIMIT ${n}`,
    [PUB]
  );
  if (preferred.length >= n) return preferred;

  const exclude = preferred.map((r) => Number(r.newsid)).filter((x) => Number.isFinite(x));
  const need = n - preferred.length;
  let filler: NewsCard[];
  if (exclude.length) {
    filler = await query<NewsCard>(
      `SELECT ${CARD_COLS}
       FROM news n
       LEFT JOIN categories c ON c.id = n.category
       WHERE n.status = ? AND ${NOT_VIDEO}
         AND n.newsid NOT IN (${exclude.join(",")})
       ORDER BY n.newsid DESC
       LIMIT ${need}`,
      [PUB]
    );
  } else {
    filler = await query<NewsCard>(
      `SELECT ${CARD_COLS}
       FROM news n
       LEFT JOIN categories c ON c.id = n.category
       WHERE n.status = ? AND ${NOT_VIDEO}
       ORDER BY n.newsid DESC
       LIMIT ${need}`,
      [PUB]
    );
  }
  return [...preferred, ...filler];
}

export async function getLead(): Promise<NewsCard | null> {
  const slider = await query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND n.slider = 'Yes' AND ${NOT_VIDEO}
     ORDER BY CAST(n.slider_priority AS UNSIGNED) ASC, n.newsid DESC
     LIMIT 1`,
    [PUB]
  );
  if (slider[0]) return slider[0];
  const latest = await getTaza(1);
  return latest[0] || null;
}

export async function getLatest(limit = 20, exclude: number[] = []): Promise<NewsCard[]> {
  const n = Math.min(Math.max(Number(limit) || 20, 1), 50);
  if (!exclude.length) {
    return getTaza(n);
  }
  const ids = exclude.map(Number).filter((x) => Number.isFinite(x));
  if (!ids.length) return getTaza(n);

  // Same preference as getTaza, but skip excluded ids
  const preferred = await query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND ${NOT_VIDEO} AND n.latest_news = 'Yes'
       AND n.newsid NOT IN (${ids.join(",")})
     ORDER BY CAST(n.latest_priority AS UNSIGNED) ASC, n.newsid DESC
     LIMIT ${n}`,
    [PUB]
  );
  if (preferred.length >= n) return preferred;
  const moreExclude = [...ids, ...preferred.map((r) => Number(r.newsid))];
  const need = n - preferred.length;
  const filler = await query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND ${NOT_VIDEO}
       AND n.newsid NOT IN (${moreExclude.join(",")})
     ORDER BY n.newsid DESC
     LIMIT ${need}`,
    [PUB]
  );
  return [...preferred, ...filler];
}

export async function getNewsByCategory(catId: number, page = 1, perPage = 20): Promise<NewsCard[]> {
  const offset = (Math.max(1, page) - 1) * perPage;
  const take = Math.min(Math.max(perPage, 1), 40);
  const cat = String(catId);
  return query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND ${inCategorySql("n")} AND ${NOT_VIDEO}
     ORDER BY n.newsid DESC
     LIMIT ${take} OFFSET ${offset}`,
    [PUB, cat, cat]
  );
}

export async function countNewsByCategory(catId: number): Promise<number> {
  const cat = String(catId);
  const rows = await query<{ total: number }>(
    `SELECT COUNT(*) AS total
     FROM news n
     WHERE n.status = ? AND ${inCategorySql("n")} AND ${NOT_VIDEO}`,
    [PUB, cat, cat]
  );
  return Number(rows[0]?.total || 0);
}

export async function getNewsByAuthor(teamId: number, limit = 20): Promise<NewsCard[]> {
  const n = Math.min(Math.max(Number(limit) || 20, 1), 40);
  return query<NewsCard>(
    `SELECT ${CARD_COLS}
     FROM news n
     LEFT JOIN categories c ON c.id = n.category
     WHERE n.status = ? AND n.team_id = ? AND ${NOT_VIDEO}
     ORDER BY n.newsid DESC
     LIMIT ${n}`,
    [PUB, teamId]
  );
}

export async function getTeam(id: number): Promise<Team | null> {
  const rows = await query<Team>(
    `SELECT t_id, name, email, designation, image, fb_link, tw_link FROM team WHERE t_id = ? LIMIT 1`,
    [id]
  );
  return rows[0] || null;
}

export async function getAd(position = 3): Promise<Ad | null> {
  const rows = await query<Ad>(
    `SELECT link, image, title FROM ads WHERE position = ? ORDER BY ad_id DESC LIMIT 1`,
    [position]
  );
  return rows[0] || null;
}

export async function getPages(): Promise<SitePage[]> {
  return query<SitePage>(`SELECT page, page_url FROM pages ORDER BY p_id ASC`);
}

export type TopicSection = {
  cat: Category;
  items: NewsCard[];
  districts: Category[];
};

/**
 * Homepage topic rows from real menu=Yes top-level categories.
 * Empty sections (no Published matches) are omitted.
 */
export async function getTopicSections(limit = 12): Promise<TopicSection[]> {
  const take = Math.min(Math.max(limit, 1), 16);
  const cats = await getNavCategories();
  const capped = cats.slice(0, take);
  const stateIds = new Set(await getStateParentIds());

  const pairs = await Promise.all(
    capped.map(async (cat) => {
      const items = await getNewsByCategory(cat.id, 1, 8);
      if (!items.length) return null;
      const districts = stateIds.has(String(cat.id)) ? await getChildCategories(cat.id) : [];
      return { cat, items, districts };
    })
  );
  return pairs.filter((p): p is TopicSection => !!p);
}
