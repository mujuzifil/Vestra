import { NextRequest, NextResponse } from "next/server";
import { revalidatePath, revalidateTag } from "next/cache";

/**
 * Receives catalog change notifications from the Laravel admin backend
 * so public product/category pages refresh without manual cache clearing.
 */
export async function POST(request: NextRequest) {
  const secret = request.headers.get("x-revalidate-secret");
  const expected = process.env.REVALIDATE_SECRET;

  if (!expected || secret !== expected) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }

  let payload: {
    paths?: string[];
    tags?: string[];
    type?: string;
  } = {};

  try {
    payload = await request.json();
  } catch {
    payload = {};
  }

  const paths = Array.isArray(payload.paths) ? payload.paths : ["/products", "/"];
  const tags = Array.isArray(payload.tags) ? payload.tags : ["products", "categories"];

  for (const path of paths) {
    if (typeof path === "string" && path.startsWith("/")) {
      revalidatePath(path);
    }
  }

  for (const tag of tags) {
    if (typeof tag === "string" && tag.length > 0) {
      revalidateTag(tag);
    }
  }

  return NextResponse.json({
    revalidated: true,
    paths,
    tags,
    type: payload.type ?? null,
    now: Date.now(),
  });
}
