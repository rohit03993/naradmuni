import { faviconSrc, logoSrc } from "@/lib/images";
import { getSiteSettings } from "@/lib/settings";

export type Branding = {
  logoUrl: string;
  faviconUrl: string;
  logoFile: string;
  faviconFile: string;
};

let cache: { at: number; data: Branding } | null = null;
const TTL = 60_000; // 1 min — admin uploads show up quickly

export async function getBranding(): Promise<Branding> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  const settings = await getSiteSettings(["brand_logo", "brand_favicon"]);
  const logoFile = (settings.brand_logo || "").trim();
  const faviconFile = (settings.brand_favicon || "").trim();
  const data: Branding = {
    logoFile,
    faviconFile,
    logoUrl: logoSrc(logoFile || null),
    faviconUrl: faviconSrc(faviconFile || null),
  };
  cache = { at: Date.now(), data };
  return data;
}
