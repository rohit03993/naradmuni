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

/** Placeholder Shorts so homepage layout is visible before API is connected.
 *  Uses real public YouTube video IDs so inline play works for UI preview.
 */
export function getDemoShorts(): YoutubeShort[] {
  return [
    {
      id: "aqz-KE-bpKQ",
      title: "Big Buck Bunny — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/aqz-KE-bpKQ/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=aqz-KE-bpKQ",
    },
    {
      id: "LXb3EKWsInQ",
      title: "Nature clip — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/LXb3EKWsInQ/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=LXb3EKWsInQ",
    },
    {
      id: "ScMzIvxBSi4",
      title: "Ocean view — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/ScMzIvxBSi4/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=ScMzIvxBSi4",
    },
    {
      id: "eRsGyueVLvQ",
      title: "City lights — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/eRsGyueVLvQ/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=eRsGyueVLvQ",
    },
    {
      id: "C0DPdy98e4c",
      title: "Test media — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/C0DPdy98e4c/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=C0DPdy98e4c",
    },
    {
      id: "hFZFjoX2cGg",
      title: "Travel reel — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/hFZFjoX2cGg/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=hFZFjoX2cGg",
    },
    {
      id: "tgbNymZ7vqY",
      title: "W3Schools sample — डेमो",
      thumb: "https://i.ytimg.com/vi/tgbNymZ7vqY/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=tgbNymZ7vqY",
    },
    {
      id: "D0UnqGm_miA",
      title: "Ambient clip — डेमो प्ले",
      thumb: "https://i.ytimg.com/vi/D0UnqGm_miA/hqdefault.jpg",
      url: "https://www.youtube.com/watch?v=D0UnqGm_miA",
    },
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