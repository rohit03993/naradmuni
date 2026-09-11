export function stripScripts(html: string): string {
  return html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, "");
}

/** mysql2 may return LONGTEXT/BLOB as Buffer */
export function asHtmlString(value: unknown): string {
  if (value == null) return "";
  if (typeof value === "string") return value;
  if (typeof Buffer !== "undefined" && Buffer.isBuffer(value)) {
    return value.toString("utf8");
  }
  if (value instanceof Uint8Array) {
    return new TextDecoder("utf-8").decode(value);
  }
  return String(value);
}

function isVisuallyEmpty(inner: string): boolean {
  // Keep blocks that still show media / structure
  if (/<(img|iframe|video|table|ul|ol|hr)\b/i.test(inner)) return false;

  const text = inner
    .replace(/<br\s*\/?>/gi, "")
    .replace(/<[^>]+>/g, "")
    .replace(/&nbsp;/gi, " ")
    .replace(/&#160;/gi, " ")
    .replace(/\u00a0/g, " ")
    .replace(/\s+/g, "")
    .trim();
  return text.length === 0;
}

function lightSanitize(html: string): string {
  let out = stripScripts(html || "");
  out = out.replace(/<div[^>]*id=["']?cke_pastebin["']?[^>]*>[\s\S]*?<\/div>/gi, "");
  out = out.replace(/&nbsp;/gi, " ");
  return out.trim();
}

/**
 * Clean legacy CKEditor HTML that creates huge vertical gaps.
 * Does not rewrite image src paths (SEO/asset safety).
 * If aggressive clean would wipe real text, falls back to a light clean.
 */
export function sanitizeArticleHtml(html: string): string {
  const raw = asHtmlString(html);
  if (!raw.trim()) return "";

  let out = lightSanitize(raw);

  // Strip ALL inline styles except on images (old content uses margin/line-height/font that blow gaps)
  out = out.replace(/<(?!img\b)([a-z0-9]+)([^>]*?)\sstyle=(["'])[\s\S]*?\3([^>]*)>/gi, "<$1$2$4>");

  // Remove empty blocks even when they wrap empty spans/strong/em
  for (let pass = 0; pass < 6; pass++) {
    out = out.replace(/<(p|div|span|h[1-6])(\s[^>]*)?>([\s\S]*?)<\/\1>/gi, (full, tag, attrs, inner) => {
      if (isVisuallyEmpty(inner)) return "";
      return full;
    });
  }

  // Single <br> → space; 2+ <br> → one paragraph break
  out = out.replace(/(?:<br\s*\/?>\s*){2,}/gi, "</p><p>");
  out = out.replace(/<br\s*\/?>/gi, " ");

  // Clean empty paragraphs created by br conversion
  for (let pass = 0; pass < 4; pass++) {
    out = out.replace(/<p(\s[^>]*)?>([\s\S]*?)<\/p>/gi, (full, _a, inner) => (isVisuallyEmpty(inner) ? "" : full));
    out = out.replace(/<div(\s[^>]*)?>([\s\S]*?)<\/div>/gi, (full, _a, inner) => (isVisuallyEmpty(inner) ? "" : full));
  }

  // Unwrap pointless single-child div nests: <div><div>…</div></div>
  for (let pass = 0; pass < 3; pass++) {
    out = out.replace(/<div[^>]*>\s*<div([^>]*)>/gi, "<div$1>");
    out = out.replace(/<\/div>\s*<\/div>/gi, "</div>");
  }

  out = out.replace(/[ \t]+\n/g, "\n");
  out = out.replace(/\n{3,}/g, "\n\n");
  out = out.replace(/(<\/p>)\s*(<p>)/gi, "$1$2");
  out = out.replace(/\s{2,}/g, " ");
  out = out.trim();

  // Old news safety: never wipe a body that had real text/media
  if (isVisuallyEmpty(out) && !isVisuallyEmpty(raw)) {
    return lightSanitize(raw);
  }

  // Empty CKEditor shells (e.g. <div style="text-align:justify"> </div>) → no body
  if (isVisuallyEmpty(out)) {
    return "";
  }

  return out;
}

export function splitHtmlAfterBlocks(html: string, count = 2): [string, string] {
  const clean = sanitizeArticleHtml(html || "");
  if (!clean) return ["", ""];

  const re = /<\/(p|div|h2|h3)>/gi;
  let n = 0;
  let idx = -1;
  let m: RegExpExecArray | null;
  while ((m = re.exec(clean))) {
    n += 1;
    if (n >= count) {
      idx = m.index + m[0].length;
      break;
    }
  }
  if (idx === -1) return [clean, ""];
  return [clean.slice(0, idx), clean.slice(idx)];
}

export function plainText(html: string, max = 4000): string {
  return asHtmlString(html)
    .replace(/<[^>]+>/g, " ")
    .replace(/&nbsp;/g, " ")
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, max);
}

export function formatStamp(date?: string | null, time?: string | null): string {
  const d = (date || "").trim();
  const t = (time || "").trim();
  if (!d && !t) return "";
  return `${d}${t ? ` ${t}` : ""} IST`;
}
