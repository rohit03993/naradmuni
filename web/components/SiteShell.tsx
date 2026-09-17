import CopyGuard from "./CopyGuard";
import Header from "./Header";
import Footer from "./Footer";
import CitiesRail from "./CitiesRail";
import PwaClient from "./PwaClient";
import InstallAppButton from "./InstallAppButton";
import type { SocialLinks } from "@/lib/branding";
import type { Ad, Category, SitePage } from "@/lib/types";
import { logoSrc } from "@/lib/images";

export default function SiteShell({
  children,
  nav,
  cities,
  pages,
  dbError,
  logoUrl,
  iconUrl = "/icons/nm-192.png",
  social,
}: {
  children: React.ReactNode;
  nav: Category[];
  cities: Category[];
  ad?: Ad | null;
  pages: SitePage[];
  dbError?: string;
  logoUrl?: string;
  iconUrl?: string;
  social?: SocialLinks;
}) {
  const logo = logoUrl || logoSrc();
  return (
    <>
      <CopyGuard />
      <Header nav={nav} cities={cities} logoUrl={logo} iconUrl={iconUrl} />
      {dbError ? (
        <div
          style={{
            maxWidth: 960,
            margin: "12px auto",
            padding: "12px 16px",
            background: "#FEF2F2",
            border: "1px solid #FECACA",
            borderRadius: 8,
            color: "#991B1B",
            fontSize: 14,
          }}
        >
          Database not reachable. Open <strong>XAMPP Control Panel</strong> and start{" "}
          <strong>MySQL</strong>, then refresh. ({dbError})
        </div>
      ) : null}
      <div className="layout">
        <div>
          <InstallAppButton iconUrl={iconUrl} />
          {children}
        </div>
        <aside className="rail">
          <CitiesRail cities={cities} />
        </aside>
      </div>
      <Footer pages={pages} logoUrl={logo} social={social} />
      <PwaClient iconUrl={iconUrl} />
    </>
  );
}
