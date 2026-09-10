import type { NextConfig } from "next";

const PHP = (process.env.PHP_ORIGIN || "http://127.0.0.1:8080").replace(/\/$/, "");

const nextConfig: NextConfig = {
  trailingSlash: false,
  images: { unoptimized: true },
  async headers() {
    return [
      {
        source: "/firebase-messaging-sw.js",
        headers: [
          { key: "Cache-Control", value: "no-cache, no-store, must-revalidate" },
          { key: "Service-Worker-Allowed", value: "/" },
        ],
      },
      {
        source: "/manifest.webmanifest",
        headers: [{ key: "Content-Type", value: "application/manifest+json" }],
      },
      // Cache news/logo images (filenames are content hashes)
      {
        source: "/naradmuni/images/:path*",
        headers: [{ key: "Cache-Control", value: "public, max-age=31536000, immutable" }],
      },
      {
        source: "/naradmuni/team/:path*",
        headers: [{ key: "Cache-Control", value: "public, max-age=86400" }],
      },
      {
        source: "/naradmuni/ads/:path*",
        headers: [{ key: "Cache-Control", value: "public, max-age=3600" }],
      },
    ];
  },
  async redirects() {
    return [
      { source: "/admin", destination: "/naradmuni/admin/dashboard.php", permanent: false },
      { source: "/admin/:path*", destination: "/naradmuni/admin/:path*", permanent: false },
      { source: "/login", destination: "/naradmuni/manage.php", permanent: false },
      { source: "/manage.php", destination: "/naradmuni/manage.php", permanent: false },
    ];
  },
  async rewrites() {
    // fallback = proxy to PHP only when no file exists under web/public/
    // Images are symlinked into public/naradmuni/* so Next serves them fast.
    return {
      fallback: [{ source: "/naradmuni/:path*", destination: `${PHP}/naradmuni/:path*` }],
    };
  },
};

export default nextConfig;
