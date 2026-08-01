"use client";

import { useEffect, useState, useCallback } from "react";
import { Loader2 } from "lucide-react";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";
import { getBlogCategories, getBlogPosts, getFeaturedPost } from "@/lib/api/blog";
import type { BlogCategory, BlogPost, BlogPostFilters, PaginatedBlogPosts } from "@/types";
import { BlogHero } from "./_components/blog-hero";
import { FeaturedArticleSection } from "./_components/featured-article-section";
import { CategoriesSection } from "./_components/categories-section";
import { SearchFilterSection } from "./_components/search-filter-section";
import { ArticlesGridSection } from "./_components/articles-grid-section";
import { NewsletterSection } from "./_components/newsletter-section";
import { ResourcesSection } from "./_components/resources-section";
import { BlogFaqSection } from "./_components/faq-section";
import { FinalCTASection } from "./_components/final-cta-section";

const initialPosts: PaginatedBlogPosts = {
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
};

export function BlogPageClient() {
  const [filters, setFilters] = useState<BlogPostFilters>({ sort: "newest" });
  const [featured, setFeatured] = useState<BlogPost | null>(null);
  const [categories, setCategories] = useState<BlogCategory[]>([]);
  const [posts, setPosts] = useState<PaginatedBlogPosts>(initialPosts);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchPosts = useCallback(async (currentFilters: BlogPostFilters) => {
    try {
      const data = await getBlogPosts(currentFilters);
      setPosts(data);
      setError(null);
    } catch {
      setError("Unable to load articles. Please try again shortly.");
      setPosts(initialPosts);
    }
  }, []);

  useEffect(() => {
    let cancelled = false;

    async function bootstrap() {
      setLoading(true);
      try {
        const [featuredData, categoriesData] = await Promise.all([
          getFeaturedPost(),
          getBlogCategories(),
        ]);
        if (!cancelled) {
          setFeatured(featuredData);
          setCategories(categoriesData);
        }
      } catch {
        if (!cancelled) {
          setError("Unable to load the Knowledge Centre. Please try again shortly.");
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    bootstrap();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    const timer = setTimeout(() => {
      fetchPosts(filters);
    }, 300);

    return () => clearTimeout(timer);
  }, [filters, fetchPosts]);

  const handleSearchChange = (value: string) => {
    setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
  };

  const handleCategoryChange = (value: string) => {
    setFilters((prev) => ({ ...prev, category: value || undefined, page: 1 }));
  };

  const handleSortChange = (value: string) => {
    setFilters((prev) => ({ ...prev, sort: value as BlogPostFilters["sort"], page: 1 }));
  };

  if (loading) {
    return (
      <main>
        <BlogHero />
        <div className="min-h-[50vh] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
        </div>
      </main>
    );
  }

  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "Blog", url: "https://vestradetergents.com/blog" },
        ])}
      />
      <main>
        <BlogHero />
        <FeaturedArticleSection post={featured} />
        <CategoriesSection
          categories={categories}
          selected={filters.category}
          onSelect={handleCategoryChange}
        />
        <SearchFilterSection
          search={filters.search ?? ""}
          onSearchChange={handleSearchChange}
          categories={categories}
          selectedCategory={filters.category ?? ""}
          onCategoryChange={handleCategoryChange}
          sort={filters.sort ?? "newest"}
          onSortChange={handleSortChange}
          resultCount={posts.total}
        />

        {error ? (
          <div className="py-20 text-center text-danger-600 bg-white" role="alert">
            {error}
          </div>
        ) : (
          <ArticlesGridSection posts={posts.data} />
        )}

        <NewsletterSection />
        <ResourcesSection />
        <BlogFaqSection />
        <FinalCTASection />
      </main>
    </>
  );
}
