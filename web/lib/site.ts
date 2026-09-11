import {
  getDistrictsWithNews,
  getMainNavCategories,
  getPages,
} from "./queries";
import type { Category, SitePage } from "./types";

type Chrome = {
  nav: Category[];
  cities: Category[];
  pages: SitePage[];
  dbError?: string;
};

const empty: Chrome = {
  nav: [],
  cities: [],
  pages: [],
};

let cache: { at: number; data: Chrome } | null = null;
const TTL = 5 * 60_000; // 5 min — fewer DB hits, faster repeat visits

export async function getSiteChrome(): Promise<Chrome> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  try {
    // Parallel + cache — no taza rail query (replaced by cities list)
    const [nav, cities, pages] = await Promise.all([
      getMainNavCategories(),
      getDistrictsWithNews(),
      getPages(),
    ]);

    const data: Chrome = {
      nav,
      cities,
      pages,
    };
    cache = { at: Date.now(), data };
    return data;
  } catch (err) {
    const dbError = err instanceof Error ? err.message : String(err);
    return { ...empty, dbError };
  }
}
