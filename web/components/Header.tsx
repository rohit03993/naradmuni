"use client";

import { useEffect, useState } from "react";
import { usePathname } from "next/navigation";
import type { Category } from "@/lib/types";
import { logoSrc } from "@/lib/images";
import CityModal from "./CityModal";

export default function Header({
  nav,
  cities,
  logoUrl,
}: {
  nav: Category[];
  cities: Category[];
  logoUrl?: string;
}) {
  const path = usePathname() || "/";
  const active = path.startsWith("/category/") ? decodeURIComponent(path.split("/")[2] || "") : "";
  const isActive = (slug: string) => active.toLowerCase() === slug.toLowerCase();
  const [cityOpen, setCityOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [logoOk, setLogoOk] = useState(true);
  const logo = logoUrl || logoSrc();

  useEffect(() => {
    if (!menuOpen) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [menuOpen]);

  useEffect(() => {
    setMenuOpen(false);
  }, [path]);

  useEffect(() => {
    setLogoOk(true);
  }, [logo]);

  const closeMenu = () => setMenuOpen(false);
  const openCity = () => {
    setMenuOpen(false);
    setCityOpen(true);
  };

  const popularCities = cities.filter((c) => c.cat_url).slice(0, 12);
  // Top strip: cities that have news (fast scroll). Side menu: main categories only.
  const topCities = cities.filter((c) => c.cat_url).slice(0, 24);

  return (
    <>
      <div className="site-chrome">
        <header className="header">
          <div className="header-inner">
            <div className="header-left">
              <button
                type="button"
                className="hamburger"
                aria-label="मेनू खोलें"
                aria-expanded={menuOpen}
                onClick={() => setMenuOpen(true)}
              >
                <span />
                <span />
                <span />
              </button>
              <a href="/" className="logo">
                {logoOk ? (
                  <img src={logo} alt="The Naradmuni" onError={() => setLogoOk(false)} />
                ) : (
                  <div className="logo-fallback">
                    <span>NM</span>
                    <span>NARADMUNI</span>
                  </div>
                )}
              </a>
            </div>
            <div className="header-actions">
              <button
                type="button"
                className="city-btn"
                onClick={() => setCityOpen(true)}
                aria-label="शहर चुनें"
                title="शहर चुनें"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" />
                </svg>
                <span className="city-btn-label">शहर चुनें</span>
              </button>
              <button
                type="button"
                className="icon-btn"
                aria-label="ऐप इंस्टॉल करें"
                title="ऐप इंस्टॉल करें"
                onClick={() => {
                  if (typeof window !== "undefined") {
                    window.dispatchEvent(new Event("nm:open-install"));
                  }
                }}
              >
                <img src="/icons/nm-192.png" alt="" width={22} height={22} style={{ borderRadius: 5 }} />
              </button>
              <button
                type="button"
                className="icon-btn"
                aria-label="सूचनाएँ"
                title="सूचनाएँ चालू करें"
                onClick={() => {
                  if (typeof window !== "undefined") {
                    window.dispatchEvent(new Event("nm:enable-notifications"));
                  }
                }}
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                  <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0" />
                </svg>
              </button>
            </div>
          </div>
        </header>

        <nav className="nav" aria-label="Main">
          <div className="nav-inner">
            <a href="/" className={path === "/" ? "active" : ""} aria-label="Home">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 3 3 12h2v8h6v-6h2v6h6v-8h2z" />
              </svg>
            </a>
            {nav.map((c) =>
              c.cat_url ? (
                <a
                  key={`nav-${c.id}`}
                  href={`/category/${c.cat_url}`}
                  className={isActive(c.cat_url) ? "active" : ""}
                >
                  {c.hindi_name}
                </a>
              ) : null
            )}
            {topCities.map((c) =>
              c.cat_url ? (
                <a
                  key={`city-${c.id}`}
                  href={`/category/${c.cat_url}`}
                  className={isActive(c.cat_url) ? "active" : ""}
                >
                  {c.hindi_name}
                </a>
              ) : null
            )}
          </div>
        </nav>
      </div>

      {menuOpen ? (
        <>
          <div className="mnav-backdrop" onClick={closeMenu} aria-hidden="true" />
          <aside className="mnav" role="dialog" aria-modal="true" aria-label="मेनू">
            <div className="mnav-head">
              <a href="/" className="mnav-brand" onClick={closeMenu}>
                {logoOk ? (
                  <img src={logo} alt="The Naradmuni" />
                ) : (
                  <strong>The Naradmuni</strong>
                )}
              </a>
              <button type="button" className="mnav-close" onClick={closeMenu} aria-label="मेनू बंद करें">
                ✕
              </button>
            </div>

            <button type="button" className="mnav-city" onClick={openCity}>
              <span className="mnav-city-ico" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" />
                </svg>
              </span>
              <span>
                <strong>शहर चुनें</strong>
                <small>अपने जिले की खबरें देखें</small>
              </span>
              <span className="mnav-chev" aria-hidden="true">›</span>
            </button>

            <button
              type="button"
              className="mnav-install"
              onClick={() => {
                closeMenu();
                window.dispatchEvent(new Event("nm:open-install"));
              }}
            >
              <img src="/icons/nm-192.png" alt="" width={28} height={28} />
              <span>
                <strong>ऐप इंस्टॉल करें</strong>
                <small>Install The Naradmuni</small>
              </span>
            </button>

            <p className="mnav-label">श्रेणियाँ</p>
            <nav className="mnav-links">
              <a href="/" className={path === "/" ? "active" : ""} onClick={closeMenu}>
                <span>होम</span>
                <span className="mnav-chev" aria-hidden="true">›</span>
              </a>
              {nav.map((c) =>
                c.cat_url ? (
                  <a
                    key={c.id}
                    href={`/category/${c.cat_url}`}
                    className={isActive(c.cat_url) ? "active" : ""}
                    onClick={closeMenu}
                  >
                    <span>{c.hindi_name}</span>
                    <span className="mnav-chev" aria-hidden="true">›</span>
                  </a>
                ) : null
              )}
              <a href="/latest" onClick={closeMenu}>
                <span>ताज़ा समाचार</span>
                <span className="mnav-chev" aria-hidden="true">›</span>
              </a>
            </nav>

            {popularCities.length ? (
              <>
                <p className="mnav-label">लोकप्रिय शहर</p>
                <div className="mnav-chips">
                  {popularCities.map((c) => (
                    <a key={c.id} href={`/category/${c.cat_url}`} onClick={closeMenu}>
                      {c.hindi_name}
                    </a>
                  ))}
                </div>
              </>
            ) : null}
          </aside>
        </>
      ) : null}

      <CityModal open={cityOpen} onClose={() => setCityOpen(false)} cities={cities} />
    </>
  );
}
