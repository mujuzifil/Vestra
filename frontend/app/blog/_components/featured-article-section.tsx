"use client";

import Link from "next/link";
import Image from "next/image";
import { motion } from "framer-motion";
import { ArrowRight, Clock, Newspaper } from "lucide-react";
import { Container } from "@/components/common/container";
import { getBlogImageUrl } from "@/lib/api/blog";
import type { BlogPost } from "@/types";

interface FeaturedArticleSectionProps {
  post: BlogPost | null;
}

function formatDate(date: string | null): string {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export function FeaturedArticleSection({ post }: FeaturedArticleSectionProps) {
  if (!post) return null;

  const imageUrl = getBlogImageUrl(post.featured_image);
  const category = post.categories?.[0];

  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="featured-article-heading">
      <Container>
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center"
        >
          <Link href={`/blog/${post.slug}`} className="group relative aspect-[16/10] rounded-[24px] overflow-hidden bg-primary-50 shadow-lg">
            {imageUrl ? (
              <Image
                src={imageUrl}
                alt={post.title}
                fill
                priority
                sizes="(max-width: 1024px) 100vw, 50vw"
                className="object-cover group-hover:scale-105 transition-transform-base duration-700"
              />
            ) : (
              <div className="absolute inset-0 flex items-center justify-center">
                <Newspaper className="w-16 h-16 text-primary-200" aria-hidden="true" />
              </div>
            )}
          </Link>

          <div>
            <div className="flex items-center gap-3 text-sm text-muted mb-4">
              <span className="px-3 py-1 rounded-full bg-secondary-500/10 text-secondary-700 font-semibold">
                Featured Article
              </span>
              {category && (
                <span className="px-3 py-1 rounded-full bg-primary-50 text-primary-700 font-medium">
                  {category.name}
                </span>
              )}
            </div>
            <h2 id="featured-article-heading" className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-primary-900 mb-4 tracking-tight">
              {post.title}
            </h2>
            <p className="text-base lg:text-lg text-muted leading-relaxed mb-6">
              {post.excerpt || ""}
            </p>
            <div className="flex items-center gap-4 text-sm text-muted mb-8">
              <span className="flex items-center gap-1">
                <Clock className="w-4 h-4" aria-hidden="true" />
                {post.reading_time_minutes} min read
              </span>
              <span>
                {post.author?.name ? `By ${post.author.name}` : "VESTRA® Team"}
                {post.published_at && ` · ${formatDate(post.published_at)}`}
              </span>
            </div>
            <Link
              href={`/blog/${post.slug}`}
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-1 transition-transform-base group"
            >
              Read Article
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
            </Link>
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
