import type { Metadata } from "next";
import { notFound, redirect } from "next/navigation";
import { asHtmlString, plainText, sanitizeArticleHtml } from "@/lib/html";
import { getPageBySlug, isAdsTxtPage } from "@/lib/queries";
import { listingMeta } from "@/lib/seo";
import { getSiteUrl } from "@/lib/siteUrl";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  if (isAdsTxtPage(slug)) {
    return { title: "The Naradmuni" };
  }
  const page = await getPageBySlug(slug);
  if (!page) return { title: "The Naradmuni" };

  const title = (page.metat || page.page || "").trim() || "The Naradmuni";
  const description =
    (page.metad || "").trim() || plainText(asHtmlString(page.description), 160) || title;

  return listingMeta(title, description, `${getSiteUrl()}/page/${page.page_url}`);
}

export default async function CmsPage({ params }: Props) {
  const { slug } = await params;
  if (isAdsTxtPage(slug)) {
    redirect("/app-ads.txt");
  }
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
