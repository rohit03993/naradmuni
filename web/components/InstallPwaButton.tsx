"use client";

export default function InstallPwaButton() {
  return (
    <button
      type="button"
      onClick={() => window.dispatchEvent(new Event("nm:open-install"))}
      style={{
        display: "block",
        width: "100%",
        background: "#111",
        color: "#fff",
        fontWeight: 700,
        padding: "14px 16px",
        borderRadius: 8,
        border: 0,
        cursor: "pointer",
        marginBottom: 16,
      }}
    >
      होम स्क्रीन पर इंस्टॉल (PWA)
    </button>
  );
}
