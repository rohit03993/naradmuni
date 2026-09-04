import type { Ad } from "@/lib/types";
import { adImage } from "@/lib/images";

export default function AdRail({ ad }: { ad: Ad | null }) {
  const src = adImage(ad?.image);
  if (src && ad) {
    return (
      <a className="ad" href={ad.link || "#"} target="_blank" rel="noreferrer">
        <img src={src} alt={ad.title || "Advertisement"} />
      </a>
    );
  }
  return <div className="ad">Advertisement</div>;
}
