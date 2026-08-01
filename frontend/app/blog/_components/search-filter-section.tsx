"use client";

import { Search, SlidersHorizontal } from "lucide-react";
import { Container } from "@/components/common/container";
import type { BlogCategory } from "@/types";

interface SearchFilterSectionProps {
  search: string;
  onSearchChange: (value: string) => void;
  categories: BlogCategory[];
  selectedCategory: string;
  onCategoryChange: (value: string) => void;
  sort: string;
  onSortChange: (value: string) => void;
  resultCount: number;
}

const sortOptions = [
  { value: "newest", label: "Newest" },
  { value: "oldest", label: "Oldest" },
  { value: "popular", label: "Most Popular" },
  { value: "reading_time", label: "Reading Time" },
];

export function SearchFilterSection({
  search,
  onSearchChange,
  categories,
  selectedCategory,
  onCategoryChange,
  sort,
  onSortChange,
  resultCount,
}: SearchFilterSectionProps) {
  return (
    <section className="py-8 bg-white border-b border-default" aria-labelledby="search-filter-heading">
      <Container>
        <div className="sr-only" id="search-filter-heading">
          Search and filter articles
        </div>
        <div className="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
          <div className="relative flex-1 max-w-xl">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-placeholder" aria-hidden="true" />
            <input
              type="search"
              value={search}
              onChange={(e) => onSearchChange(e.target.value)}
              placeholder="Search articles..."
              className="w-full pl-12 pr-4 py-3 rounded-full border border-default bg-surface-page text-primary-900 placeholder:text-placeholder outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 transition-all-base"
            />
          </div>

          <div className="flex flex-col sm:flex-row gap-3">
            <div className="relative">
              <select
                value={selectedCategory}
                onChange={(e) => onCategoryChange(e.target.value)}
                aria-label="Filter by category"
                className="appearance-none w-full sm:w-56 pl-4 pr-10 py-3 rounded-full border border-default bg-surface-page text-primary-900 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 transition-all-base cursor-pointer"
              >
                <option value="">All Categories</option>
                {categories.map((category) => (
                  <option key={category.slug} value={category.slug}>
                    {category.name}
                  </option>
                ))}
              </select>
              <SlidersHorizontal className="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" aria-hidden="true" />
            </div>

            <div className="relative">
              <select
                value={sort}
                onChange={(e) => onSortChange(e.target.value)}
                aria-label="Sort articles"
                className="appearance-none w-full sm:w-48 pl-4 pr-10 py-3 rounded-full border border-default bg-surface-page text-primary-900 outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-400/20 transition-all-base cursor-pointer"
              >
                {sortOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
              <SlidersHorizontal className="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted pointer-events-none" aria-hidden="true" />
            </div>
          </div>
        </div>

        <p className="mt-4 text-sm text-muted">
          Showing {resultCount} article{resultCount !== 1 ? "s" : ""}
        </p>
      </Container>
    </section>
  );
}
