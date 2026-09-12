const ASSET = (process.env.NEXT_PUBLIC_ASSET_BASE || "http://localhost:8080/naradmuni").replace(/\/$/, "");

export const DEFAULT_LOGO_FILE = "Logo @2x.png";

export function newsImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/images/news/${encodeURIComponent(file)}`;
}

/** Small JPEG for WhatsApp/Facebook link previews (full PNG often too big). */
export function newsShareImage(file?: string | null, siteUrl?: string): string | null {
  if (!file) return null;
  const site = (siteUrl || process.env.NEXT_PUBLIC_SITE_URL || "https://news.paldigital.in").replace(/\/$/, "");
  return `${site}/api/og-image?f=${encodeURIComponent(file)}`;
}

export function teamImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/team/${encodeURIComponent(file)}`;
}

export function adImage(file?: string | null): string | null {
  if (!file) return null;
  return `${ASSET}/ads/${encodeURIComponent(file)}`;
}

/** Header/footer logo — optional filename from site_settings.brand_logo */
export function logoSrc(file?: string | null): string {
  const name = (file || "").trim() || DEFAULT_LOGO_FILE;
  return `${ASSET}/images/logo/${encodeURIComponent(name)}`;
}

/** Browser favicon from images/logo when set; else built-in public favicon */
export function faviconSrc(file?: string | null): string {
  const name = (file || "").trim();
  if (!name) return "/favicon.png";
  return `${ASSET}/images/logo/${encodeURIComponent(name)}`;
}

export function asset(path: string): string {
  return `${ASSET}${path.startsWith("/") ? path : `/${path}`}`;
}
