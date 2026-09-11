import type { Metadata, Viewport } from "next";
import { Noto_Sans_Devanagari } from "next/font/google";
import "./globals.css";
import GoogleAdSense from "@/components/GoogleAdSense";
import SiteShell from "@/components/SiteShell";
import { getSiteChrome } from "@/lib/site";
import { getSiteUrl } from "@/lib/siteUrl";

const noto = Noto_Sans_Devanagari({
  subsets: ["devanagari"],
  variable: "--font-noto",
  display: "swap",
  weight: ["400", "500", "600", "700"],
});

export const metadata: Metadata = {
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
      { url: "/icons/nm-192.png", sizes: "192x192", type: "image/png" },
      { url: "/icons/nm-512.png", sizes: "512x512", type: "image/png" },
      { url: "/favicon.png", sizes: "512x512", type: "image/png" },
    ],
    apple: [{ url: "/icons/nm-192.png", sizes: "180x180", type: "image/png" }],
    shortcut: ["/icons/nm-192.png"],
  },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
  themeColor: "#ee1c24",
};

export const revalidate = 60;

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const chrome = await getSiteChrome();
  return (
    <html lang="hi">
      <body className={noto.variable}>
        <GoogleAdSense />
        <SiteShell
          nav={chrome.nav}
          cities={chrome.cities}
          taza={chrome.taza}
          pages={chrome.pages}
          dbError={chrome.dbError}
        >
          {children}
        </SiteShell>
      </body>
    </html>
  );
}
