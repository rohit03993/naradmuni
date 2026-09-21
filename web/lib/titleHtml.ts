import { asHtmlString } from "@/lib/html";

/** Dark text colours that stay readable on a white page. */
export const TITLE_COLORS = [
  "#111111",
  "#b91c1c",
  "#c2410c",
  "#1d4ed8",
  "#15803d",
  "#6d28d9",
] as const;

/** Marker backgrounds. Text stays dark. */
export const TITLE_HIGHLIGHTS = [
  "#fde047",
  "#86efac",
  "#f9a8d4",
  "#fdba74",
  "#7dd3fc",
] as const;

export function plainTitle(raw: unknown): string {
  const html = asHtmlString(raw);
  const t = html
    .replace(/<br\s*\/?>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .replace(/&nbsp;/gi, " ")
    .replace(/&#160;/gi, " ")
    .replace(/&amp;/gi, "&")
    .replace(/&lt;/gi, "<")
    .replace(/&gt;/gi, ">")
    .replace(/&quot;/gi, '"')
    .replace(/&#39;/gi, "'")
    .replace(/\s+/g, " ")
    .trim();
  return t;
}

function normHex(raw: string): string {
  const s = raw.trim().toLowerCase();
  const rgb = s.match(/rgba?\(\s*(\d+)\s*[,/\s]\s*(\d+)\s*[,/\s]\s*(\d+)/);
  if (rgb) {
    const hex = [rgb[1], rgb[2], rgb[3]]
      .map((n) => Number(n).toString(16).padStart(2, "0"))
      .join("");
    return `#${hex}`;
  }
  const short = s.match(/^#([0-9a-f]{3})$/);
  if (short) {
    const h = short[1];
    return `#${h[0]}${h[0]}${h[1]}${h[1]}${h[2]}${h[2]}`;
  }
  if (/^#[0-9a-f]{6}$/.test(s)) return s;
  return "";
}

function nearest(hex: string, allowed: readonly string[], fallback: string): string {
  let best = fallback;
  let bestD = 99999;
  const br = parseInt(hex.slice(1, 3), 16);
  const bg = parseInt(hex.slice(3, 5), 16);
  const bb = parseInt(hex.slice(5, 7), 16);
  for (const a of allowed) {
    const d =
      Math.abs(br - parseInt(a.slice(1, 3), 16)) +
      Math.abs(bg - parseInt(a.slice(3, 5), 16)) +
      Math.abs(bb - parseInt(a.slice(5, 7), 16));
    if (d < bestD) {
      bestD = d;
      best = a;
    }
  }
  return best;
}

function esc(text: string): string {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function extractFg(attrs: string): string {
  const style = attrs.match(/(?:^|[;\s"])color\s*:\s*([^;"]+)/i);
  if (style) {
    const hex = normHex(style[1]);
    return hex ? nearest(hex, TITLE_COLORS, "#111111") : "";
  }
  const named = attrs.match(/(?:^|\s)color\s*=\s*["']?([^"'\s>]+)/i);
  if (named) {
    const hex = normHex(named[1]);
    return hex ? nearest(hex, TITLE_COLORS, "#111111") : "";
  }
  return "";
}

function extractBg(attrs: string): string {
  const style =
    attrs.match(/background-color\s*:\s*([^;"]+)/i) ||
    attrs.match(/(?:^|;|\s)background\s*:\s*([^;"]+)/i);
  if (!style) return "";
  const hex = normHex(style[1]);
  return hex ? nearest(hex, TITLE_HIGHLIGHTS, "") : "";
}

function spanHtml(text: string, fg: string, bg: string): string {
  const bits: string[] = [];
  if (fg && fg !== "#111111") bits.push(`color:${fg}`);
  if (bg) bits.push(`background-color:${bg}`);
  if (!bits.length) return text;
  return `<span style="${bits.join(";")}">${text}</span>`;
}

/** Allow only colour / highlighter spans. Everything else becomes plain text. */
export function sanitizeTitleHtml(raw: unknown): string {
  let html = asHtmlString(raw);
  if (!html.trim()) return "";
  for (let i = 0; i < 3; i++) {
    if (!/&lt;span/i.test(html) && !/&amp;lt;span/i.test(html)) break;
    html = html
      .replace(/&amp;/g, "&")
      .replace(/&lt;/g, "<")
      .replace(/&gt;/g, ">")
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'");
  }
  html = html.replace(/<script\b[\s\S]*?>[\s\S]*?<\/script>/gi, "");
  html = html.replace(/<font([^>]*)>/gi, "<span$1>");
  html = html.replace(/<\/font>/gi, "</span>");
  html = html.replace(/<(?!\/?span\b)[^>]+>/gi, "");
  for (let i = 0; i < 8; i++) {
    html = html.replace(/<span\b([^>]*)>([^<]*)<\/span>/gi, (_full, attrs, inner) => {
      if (/^\sstyle="(?:color:#[0-9a-f]{6}(?:;background-color:#[0-9a-f]{6})?|background-color:#[0-9a-f]{6})"$/i.test(String(attrs || ""))) {
        return _full;
      }
      const text = esc(
        String(inner || "")
          .replace(/&amp;/g, "&")
          .replace(/&lt;/g, "<")
          .replace(/&gt;/g, ">")
          .replace(/&quot;/g, '"')
          .replace(/&#39;/g, "'")
      );
      return spanHtml(text, extractFg(String(attrs || "")), extractBg(String(attrs || "")));
    });
  }
  for (let i = 0; i < 8; i++) {
    const next = html.replace(
      /<span\b([^>]*)>(<span style="[^"]*">[^<]*<\/span>)<\/span>/gi,
      (_full, parentAttrs, child) => {
        const pFg = extractFg(String(parentAttrs || ""));
        const pBg = extractBg(String(parentAttrs || ""));
        const cm = String(child).match(/<span style="([^"]*)">([^<]*)<\/span>/i);
        if (!cm) return _full;
        const cFg = extractFg(` style="${cm[1]}"`);
        const cBg = extractBg(` style="${cm[1]}"`);
        const fg = cFg && cFg !== "#111111" ? cFg : pFg;
        const bg = cBg || pBg;
        return spanHtml(cm[2], fg, bg);
      }
    );
    if (next === html) break;
    html = next;
  }
  const re =
    /<span style="(?:color:#[0-9a-f]{6}(?:;background-color:#[0-9a-f]{6})?|background-color:#[0-9a-f]{6})">[\s\S]*?<\/span>/gi;
  const parts: string[] = [];
  let last = 0;
  let m: RegExpExecArray | null;
  const copy = html;
  while ((m = re.exec(copy))) {
    parts.push(esc(copy.slice(last, m.index)));
    parts.push(m[0]);
    last = m.index + m[0].length;
  }
  parts.push(esc(copy.slice(last)));
  return parts.join("");
}
