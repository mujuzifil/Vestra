"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { FileText } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { Button } from "@/components/ui/button";
import { useHomeRecommendations } from "@/hooks/use-recommendations";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

function packageSizes(specifications: Record<string, string> | null): string {
  if (!specifications) return "";
  const sizes = specifications["Package Sizes"] || specifications["Sizes"] || specifications["Pack Sizes"] || "";
  return sizes;
}

export function FeaturedProductsSection() {
  const prefersReducedMotion = useReducedMotion();
  const { data: recommendations, isLoading, error, refetch } = useHomeRecommendations(6);

  const featured = recommendations?.best_sellers?.slice(0, 3) || [];

  return (
    <section id="products" className="py-24 lg:py-36 bg-white" aria-labelledby="featured-products-heading">
      <Container>
        <SectionHeader
          id="featured-products-heading"
          title="Featured Products"
          subtitle="Flagship VESTRA® solutions developed for professional cleaning and fabric care applications."
        />

        {isLoading && <SkeletonGrid count={3} />}

        {error && <ApiError message="Failed to load featured products." onRetry={refetch} />}

        {!isLoading && !error && featured.length > 0 && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {featured.map((product, index) => (
              <motion.article
                key={product.id}
                initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-3 hover:shadow-2xl hover:border-primary-300 transition-all-base flex flex-col"
              >
                <Link
                  href={`/products/${product.slug}`}
                  className="relative p-6 lg:p-7 min-h-[260px] lg:min-h-[280px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white overflow-hidden"
                >
                  <div className="absolute w-48 h-48 rounded-full bg-[radial-gradient(circle,rgba(13,59,102,0.06)_0%,transparent_70%)]" />
                  <Image
                    src={product.images[0]?.image || "/assets/images/products/placeholder.png"}
                    alt={product.name}
                    fill
                    sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                    className="relative z-10 object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                  />
                </Link>

                <div className="p-6 lg:p-7 flex-1 flex flex-col">
                  <h3 className="text-lg lg:text-xl font-bold text-text-heading mb-2">{product.name}</h3>
                  <p className="text-sm lg:text-base text-text-muted mb-4 flex-1 leading-relaxed line-clamp-2">
                    {product.short_description}
                  </p>
                  {packageSizes(product.specifications) && (
                    <p className="text-xs text-text-muted mb-4">
                      <span className="font-medium text-text-heading">Available sizes:</span>{" "}
                      {packageSizes(product.specifications)}
                    </p>
                  )}
                  <div className="flex gap-3">
                    <Button asChild variant="outline" className="flex-1 rounded-full px-4 py-2.5 h-auto text-sm">
                      <Link href={`/products/${product.slug}`}>Learn More</Link>
                    </Button>
                    <Button asChild variant="default" className="flex-1 rounded-full px-4 py-2.5 h-auto text-sm" leftIcon={<FileText className="w-4 h-4" aria-hidden="true" />}>
                      <Link href={`/request-quote?product=${encodeURIComponent(product.slug)}`} data-track="featured-product-quote">Request a Quote</Link>
                    </Button>
                  </div>
                </div>
              </motion.article>
            ))}
          </div>
        )}

        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="text-center mt-14"
        >
          <Button asChild variant="outline" className="rounded-full px-9 py-3.5 h-auto">
            <Link href="/products" data-track="view-all-products">View All Products</Link>
          </Button>
        </motion.div>
      </Container>
    </section>
  );
}
