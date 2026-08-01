"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { Newspaper } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ArticleCard } from "@/app/blog/_components/article-card";
import { getBlogPosts } from "@/lib/api/blog";
import type { BlogPost } from "@/types";

export function LatestArticlesSection() {
  const [articles, setArticles] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    getBlogPosts({ per_page: 3, sort: "newest" })
      .then((data) => {
        if (!cancelled) setArticles(data.data);
      })
      .catch(() => {
        if (!cancelled) setArticles([]);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <section id="articles" className="py-24 lg:py-36 bg-white" aria-labelledby="articles-heading">
      <Container>
        <SectionHeader
          id="articles-heading"
          title="Latest Articles"
          subtitle="Insights on fabric care, institutional cleaning, and doing business with VESTRA®."
        />

        {loading ? (
          <div className="grid md:grid-cols-3 gap-6 lg:gap-8">
            {[...Array(3)].map((_, i) => (
              <div
                key={i}
                className="h-[420px] rounded-[20px] bg-surface-card border border-default animate-pulse"
              />
            ))}
          </div>
        ) : articles.length > 0 ? (
          <div className="grid md:grid-cols-3 gap-6 lg:gap-8">
            {articles.map((article, index) => (
              <ArticleCard key={article.slug} post={article} index={index} />
            ))}
          </div>
        ) : (
          <div className="max-w-2xl mx-auto text-center">
            <div className="w-20 h-20 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-6">
              <Newspaper className="w-10 h-10 text-primary-300" aria-hidden="true" />
            </div>
            <h3 className="text-2xl font-bold text-primary-900 mb-4">Articles Coming Soon</h3>
            <p className="text-muted text-lg leading-relaxed mb-8">
              We are preparing practical guides on fabric care, institutional laundering,
              distributor best practices, and product application tips. Check back soon.
            </p>
            <Link
              href="/blog"
              className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
            >
              Visit Knowledge Centre
            </Link>
          </div>
        )}
      </Container>
    </section>
  );
}
