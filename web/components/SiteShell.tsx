import Header from "./Header";
import Footer from "./Footer";
import CitiesRail from "./CitiesRail";
import PwaClient from "./PwaClient";
import InstallAppButton from "./InstallAppButton";
import type { Ad, Category, SitePage } from "@/lib/types";
import { logoSrc } from "@/lib/images";

export default function SiteShell({
  children,
  nav,
  cities,
  pages,
  dbError,
  logoUrl,
}: {
  children: React.ReactNode;
  nav: Category[];
  cities: Category[];
  ad?: Ad | null;
  pages: SitePage[];
  dbError?: string;
  logoUrl?: string;
}) {
  const logo = logoUrl || logoSrc();
  return (
    <>
      <Header nav={nav} cities={cities} logoUrl={logo} />
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
          <InstallAppButton />
          {children}
        </div>
        <aside className="rail">
          <CitiesRail cities={cities} />
        </aside>
      </div>
      <Footer pages={pages} logoUrl={logo} />
      <PwaClient />
    </>
  );
}
