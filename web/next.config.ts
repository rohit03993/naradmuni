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
    ];
  },
  async redirects() {
    return [
      // One admin entry on :3000
      { source: "/admin", destination: "/naradmuni/admin/dashboard.php", permanent: false },
      { source: "/admin/:path*", destination: "/naradmuni/admin/:path*", permanent: false },
      { source: "/login", destination: "/naradmuni/manage.php", permanent: false },
      { source: "/manage.php", destination: "/naradmuni/manage.php", permanent: false },
    ];
  },
  async rewrites() {
    // Proxy PHP admin + images; public pages are Next routes (/ , /news/*, /category/*)
    return [{ source: "/naradmuni/:path*", destination: `${PHP}/naradmuni/:path*` }];
  },
};

export default nextConfig;
