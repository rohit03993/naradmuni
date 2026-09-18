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

let cache: { at: number; nav: Category[]; cities: Category[] } | null = null;
const TTL = 5 * 60_000; // nav/cities — pages stay fresh so footer picks up Admin → Pages

export async function getSiteChrome(): Promise<Chrome> {
  try {
    const pagesPromise = getPages();
    let nav: Category[];
    let cities: Category[];
    if (cache && Date.now() - cache.at < TTL) {
      nav = cache.nav;
      cities = cache.cities;
    } else {
      [nav, cities] = await Promise.all([getMainNavCategories(), getDistrictsWithNews()]);
      cache = { at: Date.now(), nav, cities };
    }
    const pages = await pagesPromise;
    return { nav, cities, pages };
  } catch (err) {
    const dbError = err instanceof Error ? err.message : String(err);
    return { ...empty, dbError };
  }
}
