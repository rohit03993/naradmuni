import {
  getDistricts,
  getFeaturedDistricts,
  getNavCategories,
  getPages,
  getTaza,
} from "./queries";
import type { Category, NewsCard, SitePage } from "./types";

type Chrome = {
  nav: Category[];
  cities: Category[];
  taza: NewsCard[];
  pages: SitePage[];
  dbError?: string;
};

const empty: Chrome = {
  nav: [],
  cities: [],
  taza: [],
  pages: [],
};

let cache: { at: number; data: Chrome } | null = null;
const TTL = 60_000;

export async function getSiteChrome(): Promise<Chrome> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  try {
    const [menu, districts, featured, taza, pages] = await Promise.all([
      getNavCategories(),
      getDistricts(),
      getFeaturedDistricts(6),
      getTaza(8),
      getPages(),
    ]);

    const pinned = featured.filter((f) => f.cat_url);
    const pinnedUrls = new Set(pinned.map((p) => p.cat_url.toLowerCase()));
    const rest = menu.filter((c) => c.cat_url && !pinnedUrls.has(c.cat_url.toLowerCase()));

    const data: Chrome = {
      nav: [...pinned, ...rest],
      cities: districts,
      taza,
      pages,
    };
    cache = { at: Date.now(), data };
    return data;
  } catch (err) {
    const dbError = err instanceof Error ? err.message : String(err);
    return { ...empty, dbError };
  }
}
