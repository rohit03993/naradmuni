"use client";

import { useEffect, useRef } from "react";

/** Fire-and-forget view ping so cached article HTML still counts a real open. */
export default function RecordNewsView({ newsid }: { newsid: number }) {
  const sent = useRef(false);

  useEffect(() => {
    const id = Number(newsid);
    if (!id || sent.current) return;
    sent.current = true;
    fetch("/api/news-view", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ newsid: id }),
      keepalive: true,
    }).catch(() => {});
  }, [newsid]);

  return null;
}
