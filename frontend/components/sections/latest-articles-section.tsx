"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ArrowRight, Newspaper } from "lucide-react";

/**
 * Placeholder article cards.
 * Replace with live blog posts once a CMS or blog feed is available.
 */
const articles = [
  {
    title: "Choosing the Right Detergent for Commercial Laundries",
    excerpt:
      "Key factors institutions should consider when selecting detergents for high-volume washing operations.",
  },
  {
    title: "Fabric Care Best Practices for Hotels",
    excerpt:
      "How housekeeping teams can extend linen life and maintain a premium guest experience.",
  },
  {
    title: "Becoming a VESTRA® Distributor",
    excerpt:
      "An overview of our distribution programme, support, and growth opportunities across Uganda.",
  },
];

const prefersReducedMotion =
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export function LatestArticlesSection() {
  return (
    <section id="articles" className="py-24 lg:py-36 bg-white" aria-labelledby="articles-heading">
      <Container>
        <SectionHeader
          id="articles-heading"
          title="Latest Articles"
          subtitle="Insights on fabric care, institutional cleaning, and doing business with VESTRA®."
        />

        <div className="grid md:grid-cols-3 gap-6 lg:gap-8">
          {articles.map((article, index) => (
            <motion.article
              key={index}
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              className="group flex flex-col h-full rounded-[20px] bg-white border border-default shadow-sm overflow-hidden hover:-translate-y-2 hover:shadow-xl transition-all-base"
            >
              <div className="aspect-video bg-primary-50 flex items-center justify-center">
                <Newspaper className="w-12 h-12 text-primary-200" aria-hidden="true" />
              </div>
              <div className="p-6 lg:p-7 flex-1 flex flex-col">
                <h3 className="text-lg font-bold text-primary-900 mb-2 group-hover:text-secondary-600 transition-colors-base">
                  {article.title}
                </h3>
                <p className="text-sm text-muted leading-relaxed flex-1 mb-5">{article.excerpt}</p>
                <Link
                  href="/blog"
                  data-track="article-read-more"
                  className="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700 transition-colors-base"
                >
                  Read More
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
                </Link>
              </div>
            </motion.article>
          ))}
        </div>

        <p className="sr-only">
          These article cards are placeholder content. Replace with live blog posts once the content
          feed is available.
        </p>
      </Container>
    </section>
  );
}
