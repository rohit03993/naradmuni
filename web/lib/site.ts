import {
  getDistrictsWithNews,
  getMainNavCategories,
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
const TTL = 5 * 60_000; // 5 min — fewer DB hits, faster repeat visits

export async function getSiteChrome(): Promise<Chrome> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  try {
    // Parallel + short TTL cache — keeps every page chrome cheap
    const [nav, cities, taza, pages] = await Promise.all([
      getMainNavCategories(),
      getDistrictsWithNews(),
      getTaza(8),
      getPages(),
    ]);

    const data: Chrome = {
      nav,
      cities,
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
