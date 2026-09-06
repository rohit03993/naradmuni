/** Public site origin for share links and Open Graph (no trailing slash). */
export function getSiteUrl(): string {
  const fromEnv = (process.env.NEXT_PUBLIC_SITE_URL || "").trim().replace(/\/$/, "");
  if (fromEnv) return fromEnv;
  // Baked at build time — production must set NEXT_PUBLIC_SITE_URL or use this default.
  if (process.env.NODE_ENV === "production") {
    return "https://news.paldigital.in";
  }
  return "http://localhost:3000";
}
