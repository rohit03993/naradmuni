import type { Metadata } from "next";
import { notFound } from "next/navigation";
import ArticleActions from "@/components/ArticleActions";
import ArticleByline from "@/components/ArticleByline";
import AuthorBox from "@/components/AuthorBox";
import YeBhiPadhein from "@/components/YeBhiPadhein";
import { asHtmlString, sanitizeArticleHtml } from "@/lib/html";
import { newsImage, newsShareImage } from "@/lib/images";
import { getArticleBySlug, getRelated } from "@/lib/queries";
import { getSiteUrl } from "@/lib/siteUrl";
import { buildWhatsAppFooter, getWhatsAppShareSettings } from "@/lib/whatsappShare";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const article = await getArticleBySlug(slug);
  if (!article) return { title: "The Naradmuni" };

  const site = getSiteUrl();
  const url = `${site}/news/${article.newsurl}`;
  const shareImg = newsShareImage(article.image, site);
  const description = article.metad || article.short_description || article.title;

  return {
    title: article.metat || article.title,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: "article",
      url,
      title: article.title,
      description,
      siteName: "The Naradmuni",
      locale: "hi_IN",
      images: shareImg
        ? [{ url: shareImg, width: 1200, height: 630, alt: article.title, type: "image/jpeg" }]
        : [],
    },
    twitter: {
      card: shareImg ? "summary_large_image" : "summary",
      title: article.title,
      description,
      images: shareImg ? [shareImg] : [],
    },
  };
}

export default async function NewsPage({ params }: Props) {
  const { slug } = await params;
  const article = await getArticleBySlug(slug);
  if (!article) notFound();

  const related = await getRelated(article.category, article.newsid);
  const src = newsImage(article.image);
  const rawBody = asHtmlString(article.description);
  const bodyHtml = sanitizeArticleHtml(rawBody);
  const summaryText = (article.short_description || "").trim();
  const url = `${getSiteUrl()}/news/${article.newsurl}`;
  const waSettings = await getWhatsAppShareSettings();
  const waFooter = buildWhatsAppFooter(waSettings);

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
        <ArticleActions title={article.title} url={url} waFooter={waFooter} />
      </div>
      {summaryText && summaryText !== article.title ? (
        <p className="summary">{summaryText}</p>
      ) : null}
      {src ? (
        <figure className="article-lead">
          <img src={src} alt={article.title} />
          {article.img_abt ? <figcaption className="caption">{article.img_abt}</figcaption> : null}
        </figure>
      ) : null}
      {bodyHtml ? (
        <div className="body" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
      ) : null}
      <YeBhiPadhein items={related} />
      <AuthorBox author={article.author} />
    </article>
  );
}
