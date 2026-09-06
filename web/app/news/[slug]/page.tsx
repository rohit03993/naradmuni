import type { Metadata } from "next";
import { notFound } from "next/navigation";
import ArticleActions from "@/components/ArticleActions";
import ArticleByline from "@/components/ArticleByline";
import AuthorBox from "@/components/AuthorBox";
import YeBhiPadhein from "@/components/YeBhiPadhein";
import { asHtmlString, sanitizeArticleHtml } from "@/lib/html";
import { newsImage } from "@/lib/images";
import { getArticleBySlug, getRelated } from "@/lib/queries";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const article = await getArticleBySlug(slug);
  if (!article) return { title: "The Naradmuni" };
  return {
    title: article.metat || article.title,
    description: article.metad || article.short_description || "",
    openGraph: {
      title: article.title,
      description: article.short_description || "",
      images: newsImage(article.image) ? [newsImage(article.image) as string] : [],
    },
  };
}

export default async function NewsPage({ params }: Props) {
  const { slug } = await params;
  const article = await getArticleBySlug(slug);
  if (!article) notFound();

  const related = await getRelated(article.category, article.newsid);
  const src = newsImage(article.image);
  const bodyHtml = sanitizeArticleHtml(asHtmlString(article.description)) || asHtmlString(article.description);
  const site = (process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");
  const url = `${site}/news/${article.newsurl}`;

  return (
    <article>
      {article.cat_url ? (
        <div className="crumb">
          <a href={`/category/${article.cat_url}`}>{article.hindi_name}</a>
        </div>
      ) : null}
      <h1 className="h1">{article.title}</h1>
      <div className="meta-row">
        <ArticleByline author={article.author} place={article.hindi_name} />
        <ArticleActions title={article.title} url={url} />
      </div>
      {src ? (
        <figure className="article-lead">
          <img src={src} alt={article.title} />
          {article.img_abt ? <figcaption className="caption">{article.img_abt}</figcaption> : null}
        </figure>
      ) : null}
      {bodyHtml.trim() ? (
        <div className="body" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
      ) : null}
      <YeBhiPadhein items={related} />
      <AuthorBox author={article.author} />
    </article>
  );
}
