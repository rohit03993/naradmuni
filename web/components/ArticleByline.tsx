import type { Team } from "@/lib/types";
import { teamImage } from "@/lib/images";

type Props = {
  author?: Team;
  desk?: string;
  place?: string | null;
};

export default function ArticleByline({ author, desk = "The Naradmuni", place }: Props) {
  const avatar = author ? teamImage(author.image) : null;
  const name = author?.name || desk;
  const href = author ? `/author/${author.t_id}` : undefined;

  return (
    <div className="byline byline--modern">
      <div className="byline-avatar" aria-hidden={!avatar}>
        {avatar ? <img src={avatar} alt="" width={40} height={40} /> : (
          <span className="byline-avatar-fallback">{name.slice(0, 1).toUpperCase()}</span>
        )}
      </div>
      <div className="byline-text">
        <p className="byline-author">
          <span className="byline-by">By </span>
          {href ? (
            <a href={href}>{name}</a>
          ) : (
            <strong>{name}</strong>
          )}
        </p>
        <p className="byline-desk">
          {desk}
          {place ? `, ${place}` : ""}
        </p>
      </div>
    </div>
  );
}
