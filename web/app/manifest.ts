import type { MetadataRoute } from "next";
import { getBranding, iconMimeType } from "@/lib/branding";

export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const branding = await getBranding();
  const icon192 = branding.pwaIcon192;
  const icon512 = branding.pwaIcon512;

  return {
    name: "The Naradmuni",
    short_name: "Naradmuni",
    description: "मध्य प्रदेश और छत्तीसगढ़ की ताज़ा हिंदी खबरें",
    start_url: "/",
    scope: "/",
    display: "standalone",
    orientation: "portrait-primary",
    background_color: "#000000",
    theme_color: "#ee1c24",
    lang: "hi",
    dir: "ltr",
    categories: ["news", "magazines"],
    icons: [
      { src: icon192, sizes: "192x192", type: iconMimeType(icon192), purpose: "any" },
      { src: icon512, sizes: "512x512", type: iconMimeType(icon512), purpose: "any" },
      { src: icon512, sizes: "512x512", type: iconMimeType(icon512), purpose: "maskable" },
    ],
  };
}
