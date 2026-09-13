import { formatStamp } from "@/lib/html";

/** Date-only label for news (no time). */
export default function NewsDate({
  date,
  className = "news-date",
}: {
  date?: string | null;
  className?: string;
}) {
  const stamp = formatStamp(date);
  if (!stamp) return null;
  return <time className={className} dateTime={stamp}>{stamp}</time>;
}
