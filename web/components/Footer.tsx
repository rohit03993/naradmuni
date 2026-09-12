import type { Category, SitePage } from "@/lib/types";
import { logoSrc } from "@/lib/images";

export default function Footer({
  pages,
  logoUrl,
}: {
  pages: SitePage[];
  nav?: Category[];
  logoUrl?: string;
}) {
  return (
    <footer className="footer">
      <div className="shell">
        <img src={logoUrl || logoSrc()} alt="The Naradmuni" style={{ height: 48, margin: "0 auto 16px" }} />
        <div className="footer-links">
          {pages.map((p) => (
            <a key={p.page_url} href={`/page/${p.page_url}`}>
              {p.page}
            </a>
          ))}
          <a href="https://www.facebook.com/The-Naradmuni-100115665387257" target="_blank" rel="noreferrer">
            Facebook
          </a>
          <a href="https://twitter.com/the_naradmuni" target="_blank" rel="noreferrer">
            X
          </a>
          <a href="https://www.youtube.com/channel/UCFk1xW3Qt_THywQF-rtO9LQ" target="_blank" rel="noreferrer">
            YouTube
          </a>
          <a
            href="https://api.whatsapp.com/send?phone=+917415716541&text=व्हाट्सप्प पर खबरें भेजें"
            target="_blank"
            rel="noreferrer"
          >
            WhatsApp
          </a>
        </div>
        <p className="copy">Copyright © {new Date().getFullYear()} The Naradmuni. All Rights Reserved.</p>
      </div>
    </footer>
  );
}
