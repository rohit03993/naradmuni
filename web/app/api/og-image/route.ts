import { NextRequest, NextResponse } from "next/server";
import sharp from "sharp";

export const runtime = "nodejs";
export const dynamic = "force-dynamic";

/** Compressed JPEG for WhatsApp/Facebook previews (full news PNGs are often too large). */
export async function GET(req: NextRequest) {
  const file = (req.nextUrl.searchParams.get("f") || "").trim();
  if (!file || !/^[\w.-]+\.(jpe?g|png|webp|gif)$/i.test(file)) {
    return new NextResponse("Bad request", { status: 400 });
  }

  const assetBase = (process.env.NEXT_PUBLIC_ASSET_BASE || "").replace(/\/$/, "");
  if (!assetBase) {
    return new NextResponse("Asset base not configured", { status: 500 });
  }

  const src = `${assetBase}/images/news/${encodeURIComponent(file)}`;
  let upstream: Response;
  try {
    upstream = await fetch(src, { next: { revalidate: 86400 } });
  } catch {
    return new NextResponse("Upstream fetch failed", { status: 502 });
  }
  if (!upstream.ok) {
    return new NextResponse("Image not found", { status: 404 });
  }

  const input = Buffer.from(await upstream.arrayBuffer());
  const out = await sharp(input)
    .rotate()
    .resize(1200, 630, { fit: "inside", withoutEnlargement: true })
    .jpeg({ quality: 72, mozjpeg: true })
    .toBuffer();

  return new NextResponse(new Uint8Array(out), {
    status: 200,
    headers: {
      "Content-Type": "image/jpeg",
      "Cache-Control": "public, max-age=86400, stale-while-revalidate=604800",
    },
  });
}
