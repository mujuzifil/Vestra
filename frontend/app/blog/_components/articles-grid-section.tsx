"use client";

import Link from "next/link";
import { Newspaper } from "lucide-react";
import { Container } from "@/components/common/container";
import { ArticleCard } from "./article-card";
import type { BlogPost } from "@/types";
import { Button } from "@/components/ui/button";

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
            <h2 id="articles-heading" className="text-2xl lg:text-3xl font-bold text-text-heading mb-4">
              Articles Coming Soon
            </h2>
            <p className="text-text-body text-lg leading-relaxed mb-8">
              We are preparing practical guides on fabric care, institutional laundering,
              distributor best practices, and product application tips. Check back soon,
              or reach out to our sales team for immediate assistance.
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto">
              <Link href="/request-quote">Request a Quote</Link>
            </Button>
              <Button asChild variant="outline" className="rounded-full px-6 py-3.5 h-auto">
              <Link href="/contact">Contact Sales</Link>
            </Button>
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
