import type { MetadataRoute } from "next";
import { getSiteUrl } from "@/lib/siteUrl";

export default function robots(): MetadataRoute.Robots {
  const site = getSiteUrl();
  return {
    rules: {
      userAgent: "*",
      allow: "/",
      disallow: ["/naradmuni/", "/admin", "/login", "/manage.php"],
    },
    sitemap: `${site}/sitemap.xml`,
  };
}
