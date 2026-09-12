import { faviconSrc, logoSrc } from "@/lib/images";
import { getSiteSettings } from "@/lib/settings";

export type Branding = {
  logoUrl: string;
  faviconUrl: string;
  /** Best icon for PWA / install UI (custom favicon or default nm-192) */
  iconUrl: string;
  logoFile: string;
  faviconFile: string;
};

let cache: { at: number; data: Branding } | null = null;
const TTL = 60_000; // 1 min — admin uploads show up quickly

export function iconMimeType(url: string): string {
  const u = url.toLowerCase();
  if (u.includes(".ico")) return "image/x-icon";
  if (u.includes(".jpg") || u.includes(".jpeg")) return "image/jpeg";
  if (u.includes(".webp")) return "image/webp";
  if (u.includes(".svg")) return "image/svg+xml";
  return "image/png";
}

export async function getBranding(): Promise<Branding> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  const settings = await getSiteSettings(["brand_logo", "brand_favicon"]);
  const logoFile = (settings.brand_logo || "").trim();
  const faviconFile = (settings.brand_favicon || "").trim();
  const faviconUrl = faviconSrc(faviconFile || null);
  const iconUrl = faviconFile ? faviconUrl : "/icons/nm-192.png";
  const data: Branding = {
    logoFile,
    faviconFile,
    logoUrl: logoSrc(logoFile || null),
    faviconUrl,
    iconUrl,
  };
  cache = { at: Date.now(), data };
  return data;
}
