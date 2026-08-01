"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { FileText } from "lucide-react";
import { Container } from "@/components/common/container";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { useHomeRecommendations } from "@/hooks/use-recommendations";
import { formatPrice } from "@/lib/utils";

export function FeaturedProductsSection() {
  const { data: recommendations, isLoading, error, refetch } = useHomeRecommendations(6);

  const featured = recommendations?.best_sellers?.slice(0, 3) || [];

  return (
    <section id="products" className="py-24 lg:py-36 bg-white">
      <Container>
        <motion.h2
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-primary-900 text-center mb-4 tracking-tight"
        >
          Featured Products
        </motion.h2>
        <div className="w-20 h-1 bg-gradient-to-r from-primary-500 to-green-500 rounded-full mx-auto mb-16" />

        {isLoading && <SkeletonGrid count={3} />}

        {error && (
          <ApiError
            message="Failed to load featured products."
            onRetry={refetch}
          />
        )}

        {!isLoading && !error && featured.length > 0 && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            {featured.map((product, index) => (
              <motion.div
                key={product.id}
                initial={{ opacity: 0, y: 40 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-3 hover:shadow-2xl hover:border-primary-300 transition-all-base flex flex-col"
              >
                <Link href={`/products/${product.slug}`} className="relative p-6 lg:p-7 min-h-[260px] lg:min-h-[280px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white overflow-hidden">
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
                  <h3 className="text-lg lg:text-xl font-bold text-primary-900 mb-2">{product.name}</h3>
                  <p className="text-sm lg:text-base text-muted mb-4 flex-1 leading-relaxed line-clamp-2">
                    {product.short_description}
                  </p>
                  <p className="text-xl lg:text-2xl font-extrabold text-primary-500 mb-4">
                    {formatPrice(Number(product.price))}
                  </p>
                  <div className="flex gap-3">
                    <Link
                      href={`/products/${product.slug}`}
                      className="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-full font-semibold text-sm bg-white border border-default text-primary-900 hover:bg-surface-page hover:border-primary-400 hover:text-primary-500 transition-colors-base"
                    >
                      View Details
                    </Link>
                    <Link
                      href={`/request-quote?product=${encodeURIComponent(product.slug)}`}
                      className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full font-semibold text-sm bg-secondary-600 text-white hover:bg-secondary-600/90 transition-colors-base"
                    >
                      <FileText className="w-4 h-4" />
                      Request a Quote
                    </Link>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )}

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.3 }}
          className="text-center mt-14"
        >
          <Link
            href="/products"
            className="inline-block px-9 py-3.5 rounded-full font-semibold text-primary-900 bg-white border border-default shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all-base"
          >
            View All Products
          </Link>
        </motion.div>
      </Container>
    </section>
  );
}
