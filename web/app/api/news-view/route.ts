import { NextRequest, NextResponse } from "next/server";
import { query } from "@/lib/db";

export const runtime = "nodejs";
export const dynamic = "force-dynamic";

/** One row in news_views per article open (same table the admin News list counts). */
export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const newsid = Number(body?.newsid);
    if (!Number.isInteger(newsid) || newsid < 1) {
      return NextResponse.json({ ok: false }, { status: 400 });
    }
    await query("INSERT INTO `news_views` (`newsid`) VALUES (?)", [newsid]);
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ ok: false }, { status: 500 });
  }
}
