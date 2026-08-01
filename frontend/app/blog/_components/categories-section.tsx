"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";
import type { BlogCategory } from "@/types";

interface CategoriesSectionProps {
  categories: BlogCategory[];
  selected?: string;
  onSelect?: (slug: string) => void;
}

const categoryIcons: Record<string, string> = {
  "laundry-tips": "Droplets",
  "stain-removal": "Sparkles",
  "fabric-care": "HeartHandshake",
  "commercial-laundry": "Factory",
  "hotels-hospitality": "Hotel",
  "healthcare-cleaning": "Stethoscope",
  "industrial-cleaning": "Warehouse",
  "product-spotlights": "Package",
  "industry-news": "Newspaper",
  sustainability: "Leaf",
  "business-growth": "TrendingUp",
  "detergent-technology": "FlaskConical",
};

function getCategoryIcon(slug: string): string {
  return categoryIcons[slug] ?? "Newspaper";
}

export function CategoriesSection({ categories, selected, onSelect }: CategoriesSectionProps) {
  if (categories.length === 0) return null;

  return (
    <section className="py-20 lg:py-28 bg-surface-page" aria-labelledby="categories-heading">
      <Container>
        <SectionHeader
          id="categories-heading"
          title="Explore Topics"
          subtitle="Browse articles by category to find insights relevant to your business or industry."
        />
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
          {categories.map((category, index) => {
            const isSelected = selected === category.slug;
            return (
              <AnimatedItem key={category.slug} delay={index * 0.05}>
                <button
                  type="button"
                  onClick={() => onSelect?.(isSelected ? "" : category.slug)}
                  className={`w-full text-left p-5 lg:p-6 rounded-[20px] border shadow-sm transition-all-base h-full ${
                    isSelected
                      ? "bg-primary-600 border-primary-600 text-white"
                      : "bg-surface-card border-default hover:-translate-y-1 hover:shadow-md hover:border-primary-300/50"
                  }`}
                  aria-pressed={isSelected}
                >
                  <div
                    className={`w-12 h-12 rounded-xl flex items-center justify-center mb-4 ${
                      isSelected ? "bg-white/20 text-white" : "bg-gradient-to-br from-primary-500 to-primary-400 text-white"
                    }`}
                  >
                    <Icon name={getCategoryIcon(category.slug)} className="w-6 h-6" />
                  </div>
                  <h3 className={`font-bold mb-1 ${isSelected ? "text-white" : "text-primary-900"}`}>
                    {category.name}
                  </h3>
                  {category.description && (
                    <p className={`text-sm line-clamp-2 ${isSelected ? "text-white/80" : "text-muted"}`}>
                      {category.description}
                    </p>
                  )}
                </button>
              </AnimatedItem>
            );
          })}
        </div>
      </Container>
    </section>
  );
}
