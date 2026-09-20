import { getSiteSettings } from "@/lib/settings";

export type YoutubeShort = {
  id: string;
  title: string;
  thumb: string;
  url: string;
};

type ShortsFetch = { kind: "off" } | { kind: "ok"; items: YoutubeShort[] } | { kind: "fail" };

const FRESH_MS = 10 * 60 * 1000;
const PAGE_WAIT_MS = 3500;
const YT_WAIT_MS = 8000;

let lastGood: YoutubeShort[] = [];
let lastGoodAt = 0;
let inflight: Promise<ShortsFetch> | null = null;

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

async function youtubeGet(url: string, ms: number): Promise<Response> {
  return fetch(url, {
    cache: "no-store",
    signal: AbortSignal.timeout(ms),
  });
}

async function resolveChannelId(apiKey: string, channelInput: string): Promise<string | null> {
  const parsed = parseChannelInput(channelInput);
  if (!parsed) return null;
  if (parsed.kind === "id") return parsed.value;

  const handle = encodeURIComponent(parsed.value);
  const url = `https://www.googleapis.com/youtube/v3/channels?part=id&forHandle=${handle}&key=${encodeURIComponent(apiKey)}`;
  const res = await youtubeGet(url, YT_WAIT_MS);
  if (!res.ok) return null;
  const data = (await res.json()) as { items?: { id?: string }[] };
  return data.items?.[0]?.id || null;
}

async function fetchShortsOnce(): Promise<ShortsFetch> {
  const settings = await getSiteSettings([
    "shorts_enabled",
    "youtube_api_key",
    "youtube_channel",
    "shorts_count",
  ]);

  if (settings.shorts_enabled !== "1") return { kind: "off" };
  const apiKey = (settings.youtube_api_key || "").trim();
  const channelRaw = (settings.youtube_channel || "").trim();
  if (!apiKey || !channelRaw) return { kind: "off" };

  let count = Number(settings.shorts_count || 8);
  if (!Number.isFinite(count) || count < 1) count = 8;
  if (count > 16) count = 16;

  const channelId = await resolveChannelId(apiKey, channelRaw);
  if (!channelId) return { kind: "fail" };

  const searchUrl =
    `https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=${encodeURIComponent(channelId)}` +
    `&type=video&videoDuration=short&order=date&maxResults=${count}&key=${encodeURIComponent(apiKey)}`;

  const res = await youtubeGet(searchUrl, YT_WAIT_MS);
  if (!res.ok) return { kind: "fail" };
  const data = (await res.json()) as {
    items?: {
      id?: { videoId?: string };
      snippet?: { title?: string };
    }[];
  };

  const out: YoutubeShort[] = [];
  for (const item of data.items || []) {
    const id = item.id?.videoId;
    if (!id) continue;
    out.push({
      id,
      title: item.snippet?.title || "Short",
      thumb: `https://i.ytimg.com/vi/${id}/hq720.jpg`,
      url: `https://www.youtube.com/shorts/${id}`,
    });
  }
  return out.length ? { kind: "ok", items: out } : { kind: "fail" };
}

function loadShorts(): Promise<ShortsFetch> {
  if (!inflight) {
    inflight = fetchShortsOnce()
      .then((result) => {
        if (result.kind === "ok" && result.items.length) {
          remember(result.items);
        }
        if (result.kind === "off") {
          lastGood = [];
          lastGoodAt = 0;
        }
        return result;
      })
      .finally(() => {
        inflight = null;
      });
  }
  return inflight;
}

function remember(items: YoutubeShort[]) {
  lastGood = items;
  lastGoodAt = Date.now();
}

/** Homepage Shorts — last good set is kept if YouTube is slow or errors. No demo clips. */
export async function getHomepageShorts(): Promise<{ items: YoutubeShort[]; isDemo: boolean }> {
  const fresh = lastGood.length > 0 && Date.now() - lastGoodAt < FRESH_MS;
  if (fresh) {
    return { items: lastGood, isDemo: false };
  }

  try {
    const raced = await Promise.race([
      loadShorts(),
      new Promise<null>((resolve) => {
        setTimeout(() => resolve(null), PAGE_WAIT_MS);
      }),
    ]);

    if (raced?.kind === "off") {
      lastGood = [];
      lastGoodAt = 0;
      return { items: [], isDemo: false };
    }
    if (raced?.kind === "ok" && raced.items.length) {
      remember(raced.items);
      return { items: raced.items, isDemo: false };
    }
  } catch {
    // keep last good
  }

  if (lastGood.length) {
    return { items: lastGood, isDemo: false };
  }
  return { items: [], isDemo: false };
}
