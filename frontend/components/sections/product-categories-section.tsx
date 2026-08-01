"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { Button } from "@/components/ui/button";
import { useCategories } from "@/hooks/use-categories";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

export function ProductCategoriesSection() {
  const prefersReducedMotion = useReducedMotion();
  const { data: categories, isLoading, error, refetch } = useCategories();

  return (
    <section id="categories" className="py-24 lg:py-36 bg-white" aria-labelledby="categories-heading">
      <Container>
        <SectionHeader
          id="categories-heading"
          title="Product Categories"
          subtitle="Professional-grade cleaning and fabric care solutions developed for commercial laundries, institutions, and distributors."
        />

        {isLoading && <SkeletonGrid count={4} />}

        {error && <ApiError message="Failed to load product categories." onRetry={refetch} />}

        {!isLoading && !error && categories && categories.length > 0 && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {categories.map((category, index) => (
              <motion.div
                key={category.id}
                initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.6, delay: index * 0.08 }}
              >
                <Link
                  href={`/products?category=${encodeURIComponent(category.slug)}`}
                  data-track="category-card"
                  className="group flex flex-col h-full p-6 lg:p-7 rounded-[20px] bg-white border border-default shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-primary-300 transition-all-base"
                >
                  <div className="w-14 h-14 rounded-full bg-primary-50 flex items-center justify-center text-primary-500 mb-5 group-hover:bg-secondary-50 group-hover:text-secondary-600 transition-colors-base">
                    <span className="text-2xl font-bold">{category.name.charAt(0)}</span>
                  </div>
                  <h3 className="text-lg lg:text-xl font-bold text-text-heading mb-2">{category.name}</h3>
                  <p className="text-sm text-text-muted leading-relaxed flex-1 mb-5">
                    {category.description || `Explore ${category.name} solutions.`}
                  </p>
                  <span className="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700 transition-colors-base">
                    View Products
                    <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
                  </span>
                </Link>
              </motion.div>
            ))}
          </div>
        )}

        {!isLoading && !error && categories && categories.length === 0 && (
          <div className="text-center py-12">
            <p className="text-text-muted mb-4">Categories are being updated.</p>
            <Button asChild variant="default" className="rounded-full px-6 py-3 h-auto" rightIcon={<ArrowRight className="w-4 h-4" aria-hidden="true" />}>
              <Link href="/products">Browse All Products</Link>
            </Button>
          </div>
        )}
      </Container>
    </section>
  );
}
