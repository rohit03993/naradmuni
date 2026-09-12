import type { Metadata } from "next";
import InstallPwaButton from "@/components/InstallPwaButton";
import { getBranding } from "@/lib/branding";
import { getWhatsAppShareSettings } from "@/lib/whatsappShare";

export const metadata: Metadata = {
  title: "ऐप इंस्टॉल / डाउनलोड | The Naradmuni",
  description: "The Naradmuni ऐप होम स्क्रीन पर इंस्टॉल करें या Play Store से डाउनलोड करें।",
};

export const revalidate = 60;

export default async function InstallPage() {
  const [branding, wa] = await Promise.all([getBranding(), getWhatsAppShareSettings()]);
  const store = wa.appLink || "https://onelink.to/kqnpym";

  return (
    <div style={{ maxWidth: 520, margin: "24px auto", padding: "0 16px" }}>
      <div
        style={{
          background: "#fff",
          border: "1px solid #e5e7eb",
          borderRadius: 12,
          padding: 24,
          textAlign: "center",
        }}
      >
        <img
          src={branding.iconUrl}
          alt="The Naradmuni"
          width={72}
          height={72}
          style={{ borderRadius: 14, marginBottom: 12 }}
        />
        <h1 style={{ fontSize: 22, margin: "0 0 8px" }}>The Naradmuni ऐप</h1>
        <p style={{ color: "#6b7280", fontSize: 14, lineHeight: 1.5, margin: "0 0 20px" }}>
          होम स्क्रीन पर इंस्टॉल करें (PWA), या नीचे से Play Store / App लिंक खोलें।
        </p>

        <a
          href={store}
          target="_blank"
          rel="noreferrer"
          style={{
            display: "block",
            background: "#ee1c24",
            color: "#fff",
            fontWeight: 700,
            padding: "14px 16px",
            borderRadius: 8,
            textDecoration: "none",
            marginBottom: 10,
          }}
        >
          App डाउनलोड (Play Store / OneLink)
        </a>

        <InstallPwaButton />

        <p style={{ fontSize: 13, color: "#6b7280", lineHeight: 1.5, margin: 0, textAlign: "left" }}>
          <strong>सीधा डाउनलोड लिंक:</strong>
          <br />
          <a href={store} style={{ wordBreak: "break-all" }}>
            {store}
          </a>
          <br />
          <br />
          <strong>इंस्टॉल पेज:</strong>{" "}
          <a href="/install">https://www.thenaradmuni.com/install</a>
          <br />
          <br />
          Chrome Incognito में PWA Install नहीं चलता। सामान्य Chrome विंडो इस्तेमाल करें।
        </p>
      </div>
    </div>
  );
}
