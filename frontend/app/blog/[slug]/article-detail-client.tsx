"use client";

import Link from "next/link";
import Image from "next/image";
import { ArrowLeft, Clock, Newspaper } from "lucide-react";
import { Container } from "@/components/common/container";
import { Breadcrumb } from "@/components/common/breadcrumb";
import { JsonLd, blogPostSchema } from "@/lib/structured-data";
import { getBlogImageUrl } from "@/lib/api/blog";
import type { BlogPost } from "@/types";

interface ArticleDetailClientProps {
  post: BlogPost;
}

function formatDate(date: string | null): string {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export function ArticleDetailClient({ post }: ArticleDetailClientProps) {
  const imageUrl = getBlogImageUrl(post.featured_image);

  return (
    <>
      <JsonLd data={blogPostSchema(post)} />
      <main>
        <section
          className="relative pt-28 pb-16 lg:pt-40 lg:pb-24"
          style={{
            background:
              "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 50%, var(--primary-500) 100%)",
          }}
        >
          <Container className="relative z-10">
            <Breadcrumb
              items={[
                { label: "Blog", href: "/blog" },
                { label: post.title },
              ]}
              className="mb-8"
            />
            <div className="max-w-3xl">
              <div className="flex flex-wrap items-center gap-3 text-sm text-white/80 mb-5">
                {post.categories.map((category) => (
                  <span
                    key={category.slug}
                    className="px-3 py-1 rounded-full bg-white/10 border border-white/10 font-medium"
                  >
                    {category.name}
                  </span>
                ))}
                <span className="flex items-center gap-1">
                  <Clock className="w-4 h-4" aria-hidden="true" />
                  {post.reading_time_minutes} min read
                </span>
              </div>
              <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-6 tracking-tight leading-tight">
                {post.title}
              </h1>
              <p className="text-lg text-white/75 leading-relaxed mb-6">
                {post.excerpt || ""}
              </p>
              <div className="text-sm text-white/70">
                {post.author?.name ? `By ${post.author.name}` : "VESTRA® Team"}
                {post.published_at && ` · ${formatDate(post.published_at)}`}
              </div>
            </div>
          </Container>
        </section>

        <article className="py-12 lg:py-20 bg-white">
          <Container className="max-w-3xl">
            {imageUrl && (
              <div className="relative aspect-video rounded-[24px] overflow-hidden mb-10 bg-primary-50 shadow-lg">
                <Image
                  src={imageUrl}
                  alt={post.title}
                  fill
                  priority
                  sizes="(max-width: 1024px) 100vw, 800px"
                  className="object-cover"
                />
              </div>
            )}

            {!imageUrl && (
              <div className="aspect-video rounded-[24px] bg-primary-50 flex items-center justify-center mb-10">
                <Newspaper className="w-16 h-16 text-primary-200" aria-hidden="true" />
              </div>
            )}

            <div
              className="blog-content"
              dangerouslySetInnerHTML={{ __html: post.content }}
            />
            <style jsx>{`
              .blog-content {
                color: var(--text-body);
                line-height: 1.75;
              }
              .blog-content :global(h2) {
                font-size: 1.75rem;
                font-weight: 800;
                color: var(--primary-900);
                margin-top: 2.5rem;
                margin-bottom: 1rem;
                letter-spacing: -0.025em;
              }
              .blog-content :global(h3) {
                font-size: 1.375rem;
                font-weight: 700;
                color: var(--primary-900);
                margin-top: 2rem;
                margin-bottom: 0.75rem;
              }
              .blog-content :global(p) {
                margin-bottom: 1.25rem;
                color: var(--text-muted);
              }
              .blog-content :global(a) {
                color: var(--secondary-600);
                text-decoration: underline;
              }
              .blog-content :global(a:hover) {
                color: var(--secondary-700);
              }
              .blog-content :global(strong) {
                color: var(--primary-900);
                font-weight: 700;
              }
              .blog-content :global(ul),
              .blog-content :global(ol) {
                margin-bottom: 1.25rem;
                padding-left: 1.5rem;
                color: var(--text-muted);
              }
              .blog-content :global(li) {
                margin-bottom: 0.5rem;
              }
              .blog-content :global(img) {
                border-radius: 16px;
                margin: 1.5rem 0;
                max-width: 100%;
                height: auto;
              }
              .blog-content :global(blockquote) {
                border-left: 4px solid var(--secondary-500);
                padding-left: 1.25rem;
                margin: 1.5rem 0;
                font-style: italic;
                color: var(--primary-900);
              }
            `}</style>

            {post.tags.length > 0 && (
              <div className="mt-12 pt-8 border-t border-default">
                <h2 className="text-sm font-semibold text-primary-900 uppercase tracking-wider mb-3">
                  Tags
                </h2>
                <div className="flex flex-wrap gap-2">
                  {post.tags.map((tag) => (
                    <span
                      key={tag.slug}
                      className="px-3 py-1 rounded-full bg-surface-page text-primary-700 text-sm font-medium border border-default"
                    >
                      {tag.name}
                    </span>
                  ))}
                </div>
              </div>
            )}

            <div className="mt-10">
              <Link
                href="/blog"
                className="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 hover:text-secondary-700 transition-colors-base"
              >
                <ArrowLeft className="w-4 h-4" aria-hidden="true" />
                Back to Knowledge Centre
              </Link>
            </div>
          </Container>
        </article>

        <section
          className="py-16 lg:py-24"
          style={{
            background: "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 100%)",
          }}
        >
          <Container className="max-w-3xl text-center">
            <h2 className="text-2xl sm:text-3xl font-extrabold text-white mb-4 tracking-tight">
              Need advice for your business?
            </h2>
            <p className="text-white/75 mb-8">
              Our team is ready to help you choose the right cleaning and fabric care solutions.
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <Link
                href="/request-quote"
                className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white shadow-lg hover:-translate-y-1 transition-transform-base"
              >
                Request a Quote
              </Link>
              <Link
                href="/contact"
                className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
              >
                Contact Sales
              </Link>
            </div>
          </Container>
        </section>
      </main>
    </>
  );
}
