const LIVE_SITE = "https://www.thenaradmuni.com";

/** Public site origin for share links, sitemap, and Open Graph (no trailing slash). */
export function getSiteUrl(): string {
  const fromEnv = (process.env.NEXT_PUBLIC_SITE_URL || "").trim().replace(/\/$/, "");
  if (fromEnv) return fromEnv;
  // Baked at build time — production should set NEXT_PUBLIC_SITE_URL.
  if (process.env.NODE_ENV === "production") {
    return LIVE_SITE;
  }
  return "http://localhost:3000";
}
