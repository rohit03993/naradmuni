import type { Category, SitePage } from "@/lib/types";
import type { SocialLinks } from "@/lib/branding";
import { SOCIAL_DEFAULTS } from "@/lib/branding";
import { logoSrc } from "@/lib/images";

function SocialIcon({
  href,
  label,
  children,
}: {
  href: string;
  label: string;
  children: React.ReactNode;
}) {
  if (!href) return null;
  return (
    <a className="footer-social" href={href} target="_blank" rel="noreferrer" aria-label={label} title={label}>
      {children}
    </a>
  );
}

export default function Footer({
  pages,
  logoUrl,
  social,
}: {
  pages: SitePage[];
  nav?: Category[];
  logoUrl?: string;
  social?: SocialLinks;
}) {
  const links = social || SOCIAL_DEFAULTS;
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
        </div>
        <div className="footer-socials">
          <SocialIcon href={links.facebook} label="Facebook">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H7v3h3v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z" />
            </svg>
          </SocialIcon>
          <SocialIcon href={links.x} label="X">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M18.9 2H22l-6.8 7.8L23 22h-6.5l-5.1-6.6L5.7 22H2.6l7.3-8.3L1 2h6.7l4.6 6L18.9 2zm-1.1 18h1.8L6.3 3.9H4.4L17.8 20z" />
            </svg>
          </SocialIcon>
          <SocialIcon href={links.youtube} label="YouTube">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.8 15.6V8.4L15.8 12l-6 3.6z" />
            </svg>
          </SocialIcon>
          <SocialIcon href={links.whatsapp} label="WhatsApp">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.46 1.34 4.97L2 22l5.25-1.37c1.45.79 3.08 1.21 4.79 1.21h.01c5.5 0 9.96-4.46 9.96-9.95C22 6.45 17.54 2 12.04 2zm5.8 14.24c-.24.68-1.4 1.25-1.93 1.33-.5.08-1.13.11-1.82-.11-.42-.14-.96-.31-1.66-.61-2.92-1.26-4.82-4.2-4.97-4.4-.14-.19-1.17-1.56-1.17-2.97 0-1.42.74-2.11 1-2.4.26-.28.57-.35.76-.35h.55c.17 0 .41-.07.64.49.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.56.16.28.71 1.17 1.52 1.89 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.17-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.7-.17 1.38z" />
            </svg>
          </SocialIcon>
        </div>
        <p className="copy">Copyright © {new Date().getFullYear()} The Naradmuni. All Rights Reserved.</p>
      </div>
    </footer>
  );
}
