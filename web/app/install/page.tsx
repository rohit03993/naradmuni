import type { Metadata } from "next";
import InstallPwaButton from "@/components/InstallPwaButton";
import { getBranding } from "@/lib/branding";

export const metadata: Metadata = {
  title: "Install App | The Naradmuni",
  description: "Install The Naradmuni app on your home screen.",
};

export const revalidate = 60;

export default async function InstallPage() {
  const branding = await getBranding();

  return (
    <div style={{ maxWidth: 480, margin: "32px auto", padding: "0 16px" }}>
      <div
        style={{
          background: "#fff",
          border: "1px solid #e5e7eb",
          borderRadius: 12,
          padding: 28,
          textAlign: "center",
        }}
      >
        <img
          src={branding.iconUrl}
          alt="The Naradmuni"
          width={72}
          height={72}
          style={{ borderRadius: 14, marginBottom: 14, objectFit: "contain" }}
        />
        <h1 style={{ fontSize: 22, margin: "0 0 8px", fontWeight: 700 }}>The Naradmuni</h1>
        <p style={{ color: "#6b7280", fontSize: 14, lineHeight: 1.5, margin: "0 0 22px" }}>
          Install the app on your home screen for faster access.
        </p>
        <InstallPwaButton />
        <p style={{ fontSize: 12, color: "#9ca3af", margin: "16px 0 0", lineHeight: 1.45 }}>
          Use normal Chrome (not Incognito). On iPhone use Safari → Share → Add to Home Screen.
        </p>
      </div>
    </div>
  );
}
