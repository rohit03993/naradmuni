"use client";

import { useEffect, useMemo, useState } from "react";
import type { Category } from "@/lib/types";

export default function CityModal({
  open,
  onClose,
  cities,
}: {
  open: boolean;
  onClose: () => void;
  cities: Category[];
}) {
  const [q, setQ] = useState("");

  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = prev;
      window.removeEventListener("keydown", onKey);
    };
  }, [open, onClose]);

  const filtered = useMemo(() => {
    const t = q.trim();
    if (!t) return cities;
    const lower = t.toLowerCase();
    return cities.filter(
      (c) =>
        (c.hindi_name || "").includes(t) ||
        (c.cat_url || "").toLowerCase().includes(lower) ||
        (c.latter || "").toLowerCase() === lower
    );
  }, [cities, q]);

  const groups = useMemo(() => {
    const map = new Map<string, Category[]>();
    for (const c of filtered) {
      const letter = (c.latter || c.hindi_name?.[0] || "#").toString().toUpperCase();
      if (!map.has(letter)) map.set(letter, []);
      map.get(letter)!.push(c);
    }
    return [...map.entries()].sort((a, b) => a[0].localeCompare(b[0]));
  }, [filtered]);

  if (!open) return null;

  return (
    <div className="modal" onClick={onClose} role="dialog" aria-modal="true" aria-label="शहर चुनें">
      <div className="modal-box" onClick={(e) => e.stopPropagation()}>
        <div className="modal-head">
          <h2>शहर चुनें</h2>
          <button type="button" className="modal-close" onClick={onClose} aria-label="बंद करें">
            ✕
          </button>
        </div>
        <div className="modal-tools">
          <input
            value={q}
            onChange={(e) => setQ(e.target.value)}
            placeholder="जिले का नाम खोजें"
            className="modal-search"
            autoFocus
          />
        </div>
        <div className="modal-body">
          {!filtered.length ? (
            <p className="modal-empty">कोई जिला नहीं मिला</p>
          ) : (
            groups.map(([letter, list]) => (
              <div key={letter} className="city-group">
                <div className="city-letter">{letter}</div>
                <div className="city-grid">
                  {list.map((c) =>
                    c.cat_url ? (
                      <a key={c.id} href={`/category/${c.cat_url}`} onClick={onClose}>
                        {c.hindi_name}
                      </a>
                    ) : null
                  )}
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}
