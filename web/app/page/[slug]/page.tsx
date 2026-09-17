import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { asHtmlString, plainText, sanitizeArticleHtml } from "@/lib/html";
import { getPageBySlug } from "@/lib/queries";
import { getSiteUrl } from "@/lib/siteUrl";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const page = await getPageBySlug(slug);
  if (!page) return { title: "The Naradmuni" };

  const site = getSiteUrl();
  const url = `${site}/page/${page.page_url}`;
  const title = (page.metat || page.page || "").trim() || "The Naradmuni";
  const description =
    (page.metad || "").trim() || plainText(asHtmlString(page.description), 160) || title;

  return {
    title,
    description,
    alternates: { canonical: url },
    openGraph: {
      type: "website",
      url,
      title,
      description,
      siteName: "The Naradmuni",
      locale: "hi_IN",
    },
    twitter: {
      card: "summary",
      title,
      description,
    },
  };
}

export default async function CmsPage({ params }: Props) {
  const { slug } = await params;
  const page = await getPageBySlug(slug);
  if (!page) notFound();

  const bodyHtml = sanitizeArticleHtml(asHtmlString(page.description));

  return (
    <article className="static-page">
      <div className="crumb">
        <a href="/">Home</a>
        <span> / {page.page}</span>
      </div>
      <h1 className="h1">{page.page}</h1>
      {bodyHtml ? (
        <div className="body" dangerouslySetInnerHTML={{ __html: bodyHtml }} />
      ) : (
        <p className="static-page-empty">This page has no content yet.</p>
      )}
    </article>
  );
}
