import type { Metadata } from "next";

/** Canonical + share tags for listing/static pages (not article OG images). */
export function listingMeta(title: string, description: string, url: string): Metadata {
  return {
    title,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: "website",
      url,
      title,
      description,
      siteName: "The Naradmuni",
      locale: "hi_IN",
    },
    twitter: {
      card: "summary",
      title,
      description,
    },
  };
}
