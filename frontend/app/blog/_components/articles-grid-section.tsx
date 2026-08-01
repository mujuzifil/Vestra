"use client";

import Link from "next/link";
import { Newspaper } from "lucide-react";
import { Container } from "@/components/common/container";
import { ArticleCard } from "./article-card";
import type { BlogPost } from "@/types";

interface ArticlesGridSectionProps {
  posts: BlogPost[];
}

export function ArticlesGridSection({ posts }: ArticlesGridSectionProps) {
  if (posts.length === 0) {
    return (
      <section id="articles" className="py-20 lg:py-28 bg-white" aria-labelledby="articles-heading">
        <Container>
          <div className="max-w-2xl mx-auto text-center">
            <div className="w-20 h-20 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-6">
              <Newspaper className="w-10 h-10 text-primary-300" aria-hidden="true" />
            </div>
            <h2 id="articles-heading" className="text-2xl lg:text-3xl font-bold text-primary-900 mb-4">
              Articles Coming Soon
            </h2>
            <p className="text-body text-lg leading-relaxed mb-8">
              We are preparing practical guides on fabric care, institutional laundering,
              distributor best practices, and product application tips. Check back soon,
              or reach out to our sales team for immediate assistance.
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Link
                href="/request-quote"
                className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all-base"
              >
                Request a Quote
              </Link>
              <Link
                href="/contact"
                className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
              >
                Contact Sales
              </Link>
            </div>
          </div>
        </Container>
      </section>
    );
  }

  return (
    <section id="articles" className="py-20 lg:py-28 bg-white" aria-labelledby="articles-heading">
      <Container>
        <h2 id="articles-heading" className="sr-only">
          Latest Articles
        </h2>
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          {posts.map((post, index) => (
            <ArticleCard key={post.slug} post={post} index={index} />
          ))}
        </div>
      </Container>
    </section>
  );
}
