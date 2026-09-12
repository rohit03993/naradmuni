import type { MetadataRoute } from "next";
import { getBranding, iconMimeType } from "@/lib/branding";

export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const branding = await getBranding();
  const icon = branding.iconUrl;
  const type = iconMimeType(icon);

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
      { src: icon, sizes: "192x192", type, purpose: "any" },
      { src: icon, sizes: "512x512", type, purpose: "any" },
      { src: icon, sizes: "512x512", type, purpose: "maskable" },
    ],
  };
}
