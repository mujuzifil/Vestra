import { Metadata } from "next";
import { notFound } from "next/navigation";
import { apiGet } from "@/lib/api/client";
import { createMetadata } from "@/lib/metadata";
import { getBlogImageUrl } from "@/lib/api/blog";
import { ArticleDetailClient } from "./article-detail-client";
import type { ApiResponse, BlogPost } from "@/types";

interface Props {
  params: Promise<{ slug: string }>;
}

async function fetchPost(slug: string): Promise<BlogPost | null> {
  try {
    const response = await apiGet<ApiResponse<BlogPost>>(`/blog/posts/${slug}`);
    return response.data;
  } catch {
    return null;
  }
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const post = await fetchPost(slug);

  if (!post) {
    return createMetadata({
      title: "Article Not Found",
      description: "The requested article could not be found.",
      pathname: `/blog/${slug}`,
    });
  }

  const image = getBlogImageUrl(post.featured_image) ?? undefined;

  return createMetadata({
    title: post.meta_title ?? post.title,
    description: post.meta_description ?? post.excerpt ?? "",
    pathname: `/blog/${post.slug}`,
    image,
  });
}

export default async function ArticlePage({ params }: Props) {
  const { slug } = await params;
  const post = await fetchPost(slug);

  if (!post) {
    notFound();
  }

  return <ArticleDetailClient post={post} />;
}
