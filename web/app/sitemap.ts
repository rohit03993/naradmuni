import type { MetadataRoute } from "next";
import {
  getPages,
  getSitemapAuthorIds,
  getSitemapCategories,
  getSitemapNews,
} from "@/lib/queries";
import { getSiteUrl } from "@/lib/siteUrl";

export const revalidate = 3600;

function lastMod(date?: string | null): Date | undefined {
  const d = (date || "").trim();
  if (!/^\d{4}-\d{2}-\d{2}/.test(d)) return undefined;
  const dt = new Date(d);
  return Number.isNaN(dt.getTime()) ? undefined : dt;
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const site = getSiteUrl();
  const [pages, categories, authors, news] = await Promise.all([
    getPages(),
    getSitemapCategories(),
    getSitemapAuthorIds(),
    getSitemapNews(),
  ]);

  const staticUrls: MetadataRoute.Sitemap = [
    { url: site, lastModified: new Date(), changeFrequency: "hourly", priority: 1 },
    { url: `${site}/latest`, lastModified: new Date(), changeFrequency: "hourly", priority: 0.8 },
  ];

  const pageUrls: MetadataRoute.Sitemap = pages
    .filter((p) => p.page_url)
    .map((p) => ({
      url: `${site}/page/${p.page_url}`,
      changeFrequency: "monthly" as const,
      priority: 0.4,
    }));

  const categoryUrls: MetadataRoute.Sitemap = categories.map((c) => ({
    url: `${site}/category/${c.cat_url}`,
    changeFrequency: "hourly" as const,
    priority: 0.7,
  }));

  const authorUrls: MetadataRoute.Sitemap = authors.map((a) => ({
    url: `${site}/author/${a.t_id}`,
    changeFrequency: "weekly" as const,
    priority: 0.3,
  }));

  const newsUrls: MetadataRoute.Sitemap = news.map((n) => ({
    url: `${site}/news/${n.newsurl}`,
    lastModified: lastMod(n.date),
    changeFrequency: "daily" as const,
    priority: 0.6,
  }));

  return [...staticUrls, ...pageUrls, ...categoryUrls, ...authorUrls, ...newsUrls];
}
