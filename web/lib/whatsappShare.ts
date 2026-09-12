import { getSiteSettings } from "@/lib/settings";

export const WA_SHARE_DEFAULTS = {
  inviteText:
    "मध्य प्रदेश एवं छत्तीसगढ़ समेत देश-विदेश की तमाम खबर पाने के लिए द नारदमुनि से अभी जुड़ें",
  groupLink: "https://chat.whatsapp.com/BkZoIpOAGBS6YFMSn2xSoM",
  appText: "देश दुनिया की खबर पाने के लिए अभी डाउनलोड करें द नारदमुनि एप\n\nDownload The TheNaradMuni App",
  appLink: "http://onelink.to/kqnpym",
} as const;

export type WhatsAppShareSettings = {
  inviteText: string;
  groupLink: string;
  appText: string;
  appLink: string;
};

/** Build the footer block appended after title + article URL */
export function buildWhatsAppFooter(s: WhatsAppShareSettings): string {
  const parts: string[] = [];
  const invite = s.inviteText.trim();
  const group = s.groupLink.trim();
  const appText = s.appText.trim();
  const appLink = s.appLink.trim();

  if (invite) parts.push(invite);
  if (group) parts.push(group);
  if (appText || appLink) {
    const appBlock = [appText, appLink].filter(Boolean).join("\n");
    if (appBlock) parts.push(appBlock);
  }
  return parts.join("\n\n").trim();
}

export function buildWhatsAppMessage(title: string, url: string, s: WhatsAppShareSettings): string {
  const footer = buildWhatsAppFooter(s);
  const head = `${title}\n${url}`.trim();
  return footer ? `${head}\n\n${footer}` : head;
}

let cache: { at: number; data: WhatsAppShareSettings } | null = null;
const TTL = 60_000;

export async function getWhatsAppShareSettings(): Promise<WhatsAppShareSettings> {
  if (cache && Date.now() - cache.at < TTL) return cache.data;

  const raw = await getSiteSettings([
    "wa_share_invite_text",
    "wa_share_group_link",
    "wa_share_app_text",
    "wa_share_app_link",
  ]);

  const data: WhatsAppShareSettings = {
    inviteText: (raw.wa_share_invite_text || "").trim() || WA_SHARE_DEFAULTS.inviteText,
    groupLink: (raw.wa_share_group_link || "").trim() || WA_SHARE_DEFAULTS.groupLink,
    appText: (raw.wa_share_app_text || "").trim() || WA_SHARE_DEFAULTS.appText,
    appLink: (raw.wa_share_app_link || "").trim() || WA_SHARE_DEFAULTS.appLink,
  };
  cache = { at: Date.now(), data };
  return data;
}
