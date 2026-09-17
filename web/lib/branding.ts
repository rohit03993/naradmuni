import { faviconSrc, logoSrc } from "@/lib/images";
import { getSiteSettings } from "@/lib/settings";

export type SocialLinks = {
  facebook: string;
  x: string;
  youtube: string;
  whatsapp: string;
};

export type Branding = {
  logoUrl: string;
  faviconUrl: string;
  /** UI / tab icon (may be JPEG from admin) */
  iconUrl: string;
  /** PNG icons Chrome needs for reliable PWA install */
  pwaIcon192: string;
  pwaIcon512: string;
  logoFile: string;
  faviconFile: string;
  social: SocialLinks;
};

export const SOCIAL_DEFAULTS: SocialLinks = {
  facebook: "https://www.facebook.com/The-Naradmuni-100115665387257",
  x: "https://twitter.com/the_naradmuni",
  youtube: "https://www.youtube.com/channel/UCFk1xW3Qt_THywQF-rtO9LQ",
  whatsapp: "https://api.whatsapp.com/send?phone=+917415716541&text=%E0%A4%B5%E0%A5%8D%E0%A4%B9%E0%A4%BE%E0%A4%9F%E0%A5%8D%E0%A4%B8%E0%A4%AA%E0%A5%8D%E0%A4%AA%20%E0%A4%AA%E0%A4%B0%20%E0%A4%96%E0%A4%AC%E0%A4%B0%E0%A5%87%E0%A4%82%20%E0%A4%AD%E0%A5%87%E0%A4%9C%E0%A5%87%E0%A4%82",
};

function pickUrl(raw: string | undefined, fallback: string): string {
  const v = (raw || "").trim();
  return v || fallback;
}

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

  const settings = await getSiteSettings([
    "brand_logo",
    "brand_favicon",
    "social_facebook",
    "social_x",
    "social_youtube",
    "social_whatsapp",
  ]);
  const logoFile = (settings.brand_logo || "").trim();
  const faviconFile = (settings.brand_favicon || "").trim();
  const faviconUrl = faviconSrc(faviconFile || null);
  const iconUrl = faviconFile ? faviconUrl : "/icons/nm-192.png";
  // Same icon as favicon for PWA home-screen when set (PNG preferred; JPEG still used if that is what admin uploaded)
  const data: Branding = {
    logoFile,
    faviconFile,
    logoUrl: logoSrc(logoFile || null),
    faviconUrl,
    iconUrl,
    pwaIcon192: iconUrl,
    pwaIcon512: iconUrl,
    social: {
      facebook: pickUrl(settings.social_facebook, SOCIAL_DEFAULTS.facebook),
      x: pickUrl(settings.social_x, SOCIAL_DEFAULTS.x),
      youtube: pickUrl(settings.social_youtube, SOCIAL_DEFAULTS.youtube),
      whatsapp: pickUrl(settings.social_whatsapp, SOCIAL_DEFAULTS.whatsapp),
    },
  };
  cache = { at: Date.now(), data };
  return data;
}
