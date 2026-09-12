import type { Metadata, Viewport } from "next";
import { Noto_Sans_Devanagari } from "next/font/google";
import "./globals.css";
import GoogleAdSense from "@/components/GoogleAdSense";
import AdsClickGuard from "@/components/AdsClickGuard";
import SiteShell from "@/components/SiteShell";
import { getBranding, iconMimeType } from "@/lib/branding";
import { getSiteChrome } from "@/lib/site";
import { getSiteUrl } from "@/lib/siteUrl";

const noto = Noto_Sans_Devanagari({
  subsets: ["devanagari"],
  variable: "--font-noto",
  display: "swap",
  weight: ["400", "500", "600", "700"],
});

export async function generateMetadata(): Promise<Metadata> {
  const branding = await getBranding();
  const fav = branding.faviconUrl;
  const icon = branding.iconUrl;
  const type = iconMimeType(icon);
  return {
    metadataBase: new URL(getSiteUrl()),
    title: "The Naradmuni | हिंदी न्यूज़ मध्य प्रदेश",
    description: "मध्य प्रदेश और छत्तीसगढ़ की ताज़ा खबरें, The Naradmuni पर।",
    applicationName: "The Naradmuni",
    manifest: "/manifest.webmanifest",
    appleWebApp: {
      capable: true,
      statusBarStyle: "default",
      title: "Naradmuni",
    },
    formatDetection: { telephone: false },
    other: {
      "google-adsense-account": "ca-pub-4403691045202329",
    },
    icons: {
      icon: [
        { url: fav, type: iconMimeType(fav) },
        { url: icon, sizes: "192x192", type },
        { url: icon, sizes: "512x512", type },
      ],
      apple: [{ url: icon, sizes: "180x180" }],
      shortcut: [fav],
    },
  };
}

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
  themeColor: "#ee1c24",
};

export const revalidate = 60;

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const [chrome, branding] = await Promise.all([getSiteChrome(), getBranding()]);
  return (
    <html lang="hi">
      <body className={noto.variable}>
        <GoogleAdSense />
        <AdsClickGuard />
        <SiteShell
          nav={chrome.nav}
          cities={chrome.cities}
          pages={chrome.pages}
          dbError={chrome.dbError}
          logoUrl={branding.logoUrl}
          iconUrl={branding.iconUrl}
        >
          {children}
        </SiteShell>
      </body>
    </html>
  );
}
