import type { Metadata } from "next";
import { notFound } from "next/navigation";
import ArticleActions from "@/components/ArticleActions";
import ArticleByline from "@/components/ArticleByline";
import AuthorBox from "@/components/AuthorBox";
import RecordNewsView from "@/components/RecordNewsView";
import NewsDate from "@/components/NewsDate";
import YeBhiPadhein from "@/components/YeBhiPadhein";
import { asHtmlString, sanitizeArticleHtml } from "@/lib/html";
import { newsImage, newsShareImage } from "@/lib/images";
import { getArticleBySlug, getRelated } from "@/lib/queries";
import { getSiteUrl } from "@/lib/siteUrl";
import { plainTitle } from "@/lib/titleHtml";
import NewsTitle from "@/components/NewsTitle";
import { buildWhatsAppFooter, getWhatsAppShareSettings } from "@/lib/whatsappShare";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const article = await getArticleBySlug(slug);
  if (!article) return { title: "The Naradmuni" };

  const site = getSiteUrl();
  const url = `${site}/news/${article.newsurl}`;
  const shareImg = newsShareImage(article.image, site);
  const headline = plainTitle(article.title);
  const description = article.metad || article.short_description || headline;

  return {
    title: article.metat || headline,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: "article",
      url,
      title: headline,
      description,
      siteName: "The Naradmuni",
      locale: "hi_IN",
      images: shareImg
        ? [{ url: shareImg, width: 1200, height: 630, alt: headline, type: "image/jpeg" }]
        : [],
    },
    twitter: {
      card: shareImg ? "summary_large_image" : "summary",
      title: headline,
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
  const headline = plainTitle(article.title);
  const summaryText = (article.short_description || "").trim();
  const url = `${getSiteUrl()}/news/${article.newsurl}`;
  const waSettings = await getWhatsAppShareSettings();
  const waFooter = buildWhatsAppFooter(waSettings);

  return (
    <article>
      <RecordNewsView newsid={Number(article.newsid)} />
      {article.cat_url ? (
        <div className="crumb">
          <a href={`/category/${article.cat_url}`}>{article.hindi_name}</a>
        </div>
      ) : null}
      <h1 className="h1"><NewsTitle html={article.title} /></h1>
      <div className="meta-row">
        <div className="meta-left">
          <ArticleByline author={article.author} place={article.hindi_name} />
          <NewsDate date={article.date} className="news-date news-date--article" />
        </div>
        <ArticleActions title={headline} url={url} waFooter={waFooter} />
      </div>
      {summaryText && summaryText !== headline ? (
        <p className="summary">{summaryText}</p>
      ) : null}
      {src ? (
        <figure className="article-lead">
          <img src={src} alt={headline} />
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
