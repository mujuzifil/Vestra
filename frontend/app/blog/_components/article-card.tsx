"use client";

import Link from "next/link";
import Image from "next/image";
import { ArrowRight, Clock, Newspaper } from "lucide-react";
import { AnimatedItem } from "@/components/common/animated-section";
import { getBlogImageUrl } from "@/lib/api/blog";
import type { BlogPost } from "@/types";

interface ArticleCardProps {
  post: BlogPost;
  index?: number;
  featured?: boolean;
}

function formatDate(date: string | null): string {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export function ArticleCard({ post, index = 0, featured = false }: ArticleCardProps) {
  const imageUrl = getBlogImageUrl(post.featured_image);
  const category = post.categories?.[0];

  return (
    <AnimatedItem delay={index * 0.1}>
      <Link
        href={`/blog/${post.slug}`}
        className="group flex flex-col h-full rounded-[20px] bg-white border border-default shadow-sm overflow-hidden hover:-translate-y-2 hover:shadow-xl transition-all-base"
      >
        <div className="relative aspect-video bg-primary-50 flex items-center justify-center overflow-hidden">
          {imageUrl ? (
            <Image
              src={imageUrl}
              alt={post.title}
              fill
              sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
              className="object-cover group-hover:scale-105 transition-transform-base duration-500"
            />
          ) : (
            <Newspaper className="w-12 h-12 text-primary-200" aria-hidden="true" />
          )}
          {featured && (
            <span className="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-semibold bg-secondary-500 text-white shadow-md">
              Featured
            </span>
          )}
        </div>
        <div className="p-6 lg:p-7 flex-1 flex flex-col">
          <div className="flex items-center gap-3 text-sm text-muted mb-3">
            {category && (
              <span className="px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 font-medium">
                {category.name}
              </span>
            )}
            <span className="flex items-center gap-1">
              <Clock className="w-3.5 h-3.5" aria-hidden="true" />
              {post.reading_time_minutes} min read
            </span>
          </div>
          <h3
            className={`font-bold text-primary-900 mb-2 group-hover:text-secondary-600 transition-colors-base ${
              featured ? "text-xl lg:text-2xl" : "text-lg"
            }`}
          >
            {post.title}
          </h3>
          <p className="text-sm text-muted leading-relaxed flex-1 mb-5 line-clamp-3">
            {post.excerpt || ""}
          </p>
          <div className="flex items-center justify-between mt-auto pt-4 border-t border-default">
            <span className="text-sm text-muted">
              {post.author?.name ? `By ${post.author.name}` : "VESTRA® Team"}
              {post.published_at && ` · ${formatDate(post.published_at)}`}
            </span>
            <span className="inline-flex items-center gap-1 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700 transition-colors-base">
              Read More
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
            </span>
          </div>
        </div>
      </Link>
    </AnimatedItem>
  );
}
