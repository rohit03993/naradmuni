import { plainTitle, sanitizeTitleHtml } from "@/lib/titleHtml";

/** Headline with optional admin colour spans. Falls back to plain text. */
export default function NewsTitle({ html }: { html: string | null | undefined }) {
  const safe = sanitizeTitleHtml(html);
  const plain = plainTitle(html);
  if (!safe) return <>{plain}</>;
  if (!safe.includes("<span")) return <>{plain}</>;
  return <span className="news-title" dangerouslySetInnerHTML={{ __html: safe }} />;
}
