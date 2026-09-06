import { unstable_cache } from "next/cache";
import { getSiteSettings } from "@/lib/settings";

export type YoutubeShort = {
  id: string;
  title: string;
  thumb: string;
  url: string;
};

function parseChannelInput(raw: string): { kind: "id" | "handle"; value: string } | null {
  const s = raw.trim();
  if (!s) return null;
  if (/^UC[\w-]{20,}$/i.test(s)) {
    return { kind: "id", value: s };
  }
  try {
    const u = new URL(s.startsWith("http") ? s : `https://${s}`);
    const parts = u.pathname.split("/").filter(Boolean);
    const channelIdx = parts.indexOf("channel");
    if (channelIdx >= 0 && parts[channelIdx + 1]) {
      return { kind: "id", value: parts[channelIdx + 1] };
    }
    const at = parts.find((p) => p.startsWith("@"));
    if (at) {
      return { kind: "handle", value: at.replace(/^@/, "") };
    }
    if (parts[0]?.startsWith("@")) {
      return { kind: "handle", value: parts[0].slice(1) };
    }
  } catch {
    // not a URL
  }
  if (s.startsWith("@")) {
    return { kind: "handle", value: s.slice(1) };
  }
  return { kind: "handle", value: s.replace(/^@/, "") };
}

async function resolveChannelId(apiKey: string, channelInput: string): Promise<string | null> {
  const parsed = parseChannelInput(channelInput);
  if (!parsed) return null;
  if (parsed.kind === "id") return parsed.value;

  const handle = encodeURIComponent(parsed.value);
  const url = `https://www.googleapis.com/youtube/v3/channels?part=id&forHandle=${handle}&key=${encodeURIComponent(apiKey)}`;
  const res = await fetch(url, { next: { revalidate: 600 } });
  if (!res.ok) return null;
  const data = (await res.json()) as { items?: { id?: string }[] };
  return data.items?.[0]?.id || null;
}

async function fetchShortsUncached(): Promise<YoutubeShort[]> {
  const settings = await getSiteSettings([
    "shorts_enabled",
    "youtube_api_key",
    "youtube_channel",
    "shorts_count",
  ]);

  if (settings.shorts_enabled !== "1") return [];
  const apiKey = (settings.youtube_api_key || "").trim();
  const channelRaw = (settings.youtube_channel || "").trim();
  if (!apiKey || !channelRaw) return [];

  let count = Number(settings.shorts_count || 8);
  if (!Number.isFinite(count) || count < 1) count = 8;
  if (count > 16) count = 16;

  const channelId = await resolveChannelId(apiKey, channelRaw);
  if (!channelId) return [];

  const searchUrl =
    `https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=${encodeURIComponent(channelId)}` +
    `&type=video&videoDuration=short&order=date&maxResults=${count}&key=${encodeURIComponent(apiKey)}`;

  const res = await fetch(searchUrl, { next: { revalidate: 600 } });
  if (!res.ok) return [];
  const data = (await res.json()) as {
    items?: {
      id?: { videoId?: string };
      snippet?: {
        title?: string;
        thumbnails?: { medium?: { url?: string }; high?: { url?: string }; default?: { url?: string } };
      };
    }[];
  };

  const out: YoutubeShort[] = [];
  for (const item of data.items || []) {
    const id = item.id?.videoId;
    if (!id) continue;
    const thumbs = item.snippet?.thumbnails;
    const thumb = thumbs?.medium?.url || thumbs?.high?.url || thumbs?.default?.url || "";
    out.push({
      id,
      title: item.snippet?.title || "Short",
      thumb,
      url: `https://www.youtube.com/shorts/${id}`,
    });
  }
  return out;
}

/** Placeholder Shorts so homepage layout is visible before API is connected */
export function getDemoShorts(): YoutubeShort[] {
  return [
    { id: "demo-1", title: "भोपाल की ताज़ा घटना — डेमो शॉर्ट", thumb: "", url: "#" },
    { id: "demo-2", title: "मध्य प्रदेश समाचार क्लिप — डेमो", thumb: "", url: "#" },
    { id: "demo-3", title: "इंदौर अपडेट 60 सेकंड में — डेमो", thumb: "", url: "#" },
    { id: "demo-4", title: "जन्माष्टमी कवरेज शॉर्ट — डेमो", thumb: "", url: "#" },
    { id: "demo-5", title: "स्पोर्ट्स हाइलाइट्स — डेमो", thumb: "", url: "#" },
    { id: "demo-6", title: "ब्रेकिंग न्यूज़ रील्स — डेमो", thumb: "", url: "#" },
    { id: "demo-7", title: "शहर की सड़क रिपोर्ट — डेमो", thumb: "", url: "#" },
    { id: "demo-8", title: "राशिफल क्विक टिप — डेमो", thumb: "", url: "#" },
  ];
}

/** Homepage Shorts — refresh ~every 10 minutes; demos if not configured yet */
export async function getHomepageShorts(): Promise<{ items: YoutubeShort[]; isDemo: boolean }> {
  const cached = unstable_cache(fetchShortsUncached, ["homepage-youtube-shorts"], {
    revalidate: 600,
  });
  try {
    const items = await cached();
    if (items.length) return { items, isDemo: false };
  } catch {
    // fall through to demos
  }
  return { items: getDemoShorts(), isDemo: true };
}