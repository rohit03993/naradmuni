import type { Team } from "@/lib/types";
import { teamImage } from "@/lib/images";

export default function AuthorBox({ author }: { author?: Team }) {
  if (!author) return null;
  const src = teamImage(author.image);
  return (
    <section className="author-box">
      {src ? <img src={src} alt={author.name} /> : <div className="ph" style={{ width: 80, height: 80, borderRadius: 9999 }} />}
      <div>
        <p className="author-kicker">लेखक के बारे में</p>
        <h3>{author.name}</h3>
        {author.designation ? <p style={{ color: "var(--muted)", marginTop: -4 }}>{author.designation}</p> : null}
        {author.email ? <p>{author.email}</p> : null}
        <a href={`/author/${author.t_id}`}>View all posts by {author.name} →</a>
      </div>
    </section>
  );
}
