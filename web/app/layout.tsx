import type { Metadata, Viewport } from "next";
import { Noto_Sans_Devanagari } from "next/font/google";
import "./globals.css";
import SiteShell from "@/components/SiteShell";
import { getSiteChrome } from "@/lib/site";

const noto = Noto_Sans_Devanagari({
  subsets: ["devanagari"],
  variable: "--font-noto",
  display: "swap",
  weight: ["400", "500", "600", "700"],
});

export const metadata: Metadata = {
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
  icons: {
    icon: [
      { url: "/icons/app-icon.png", sizes: "512x512", type: "image/png" },
      { url: "/favicon.png", sizes: "512x512", type: "image/png" },
    ],
    apple: [{ url: "/icons/app-icon.png", sizes: "180x180", type: "image/png" }],
    shortcut: ["/icons/app-icon.png"],
  },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  maximumScale: 5,
  themeColor: "#ee1c24",
};

export const dynamic = "force-dynamic";

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const chrome = await getSiteChrome();
  return (
    <html lang="hi">
      <body className={noto.variable}>
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
