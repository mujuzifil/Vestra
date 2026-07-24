"use client";

import { useState, useMemo } from "react";
import Image from "next/image";
import Link from "next/link";
import { Search, ShoppingCart, Loader2 } from "lucide-react";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { EmptyProducts } from "@/components/ui/empty-products";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { useProducts } from "@/hooks/use-products";
import { useCategories } from "@/hooks/use-categories";
import { useCartContext, toCartProduct } from "@/lib/cart-context";
import { toastAddedToCart } from "@/lib/toast-utils";
import type { Product } from "@/types";
import { formatPrice, cn } from "@/lib/utils";

function QuickAddButton({ product, disabled }: { product: Product; disabled?: boolean }) {
  const { addItem } = useCartContext();
  const [loading, setLoading] = useState(false);

  return (
    <button
      type="button"
      disabled={disabled || loading}
      onClick={async () => {
        setLoading(true);
        try {
          await addItem(toCartProduct(product), 1);
          toastAddedToCart(product.name, 1);
        } finally {
          setLoading(false);
        }
      }}
      className={cn(
        "inline-flex items-center justify-center w-10 h-10 rounded-full border border-[#e2e8f0] bg-white text-[#0a1628] hover:bg-[#f8fafc] hover:border-green-500 hover:text-green-600 transition-colors",
        (disabled || loading) && "opacity-60 cursor-not-allowed"
      )}
      aria-label="Add to cart"
    >
      {loading ? (
        <Loader2 className="w-4 h-4 animate-spin" />
      ) : (
        <ShoppingCart className="w-4 h-4" />
      )}
    </button>
  );
}

export default function ProductsPage() {
  const [query, setQuery] = useState("");
  const [activeCategory, setActiveCategory] = useState("all");

  const {
    data: products,
    isLoading: productsLoading,
    error: productsError,
    refetch: refetchProducts,
  } = useProducts();

  const {
    data: categories,
    isLoading: categoriesLoading,
    error: categoriesError,
    refetch: refetchCategories,
  } = useCategories();

  const filteredProducts = useMemo(() => {
    if (!products) return [];
    const normalized = query.toLowerCase();
    return products.filter((product) => {
      const matchesSearch =
        product.name.toLowerCase().includes(normalized) ||
        product.short_description.toLowerCase().includes(normalized) ||
        product.category.name.toLowerCase().includes(normalized);
      const matchesCategory =
        activeCategory === "all" || product.category.slug === activeCategory;
      return matchesSearch && matchesCategory;
    });
  }, [query, activeCategory, products]);

  const allCategories = useMemo(() => {
    if (!categories) return [{ id: 0, name: "All Products", slug: "all" }];
    return [{ id: 0, name: "All Products", slug: "all" }, ...categories];
  }, [categories]);

  const isLoading = productsLoading || categoriesLoading;
  const hasError = productsError || categoriesError;

  return (
    <main>
      <PageHero
        title="Our Products"
        subtitle="Explore our range of professional fabric care solutions designed for homes, laundries, and businesses."
        breadcrumb={[{ label: "Products" }]}
      />

      <section className="py-16 lg:py-24 bg-white" aria-labelledby="products-heading">
        <Container>
          <h2 id="products-heading" className="sr-only">
            Product Listing
          </h2>

          {/* Search and filters */}
          <div className="flex flex-col lg:flex-row gap-4 lg:items-center justify-between mb-10">
            <div className="relative max-w-md w-full">
              <label htmlFor="product-search" className="sr-only">
                Search products
              </label>
              <Search
                className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#94a3b8]"
                aria-hidden="true"
              />
              <input
                id="product-search"
                type="text"
                placeholder="Search products..."
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                className="w-full pl-12 pr-4 py-3 rounded-full border border-[#e2e8f0] bg-[#f8fafc] text-[#0a1628] placeholder:text-[#94a3b8] focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              />
            </div>

            <div className="flex flex-wrap gap-2" role="tablist" aria-label="Product categories">
              {isLoading ? (
                <div className="flex gap-2">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="w-24 h-9 rounded-full bg-[#e2e8f0] animate-pulse" />
                  ))}
                </div>
              ) : (
                allCategories.map((category) => (
                  <button
                    key={category.id}
                    role="tab"
                    aria-selected={activeCategory === category.slug}
                    onClick={() => setActiveCategory(category.slug)}
                    className={cn(
                      "px-4 py-2 rounded-full text-sm font-semibold transition-all",
                      activeCategory === category.slug
                        ? "bg-green-500 text-white shadow-md shadow-green-500/25"
                        : "bg-[#f1f5f9] text-[#475569] hover:bg-[#e2e8f0]"
                    )}
                  >
                    {category.name}
                  </button>
                ))
              )}
            </div>
          </div>

          {/* Error state */}
          {hasError && (
            <ApiError
              message="Failed to load products. Please try again."
              onRetry={() => {
                refetchProducts();
                refetchCategories();
              }}
            />
          )}

          {/* Loading state */}
          {isLoading && !hasError && <SkeletonGrid count={6} />}

          {/* Product grid */}
          {!isLoading && !hasError && filteredProducts.length > 0 && (
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {filteredProducts.map((product, index) => (
                <motion.article
                  key={product.id}
                  initial={{ opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.5, delay: (index % 6) * 0.08 }}
                  className="group bg-white rounded-[20px] overflow-hidden border border-[#e2e8f0] shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-[#7db8ec] transition-all flex flex-col"
                >
                  <Link
                    href={`/products/${product.slug}`}
                    className="relative p-6 lg:p-8 min-h-[240px] lg:min-h-[260px] flex items-center justify-center bg-gradient-to-b from-[#f8fafc] to-white overflow-hidden"
                  >
                    <div className="absolute w-44 h-44 rounded-full bg-[radial-gradient(circle,rgba(13,59,102,0.05)_0%,transparent_70%)]" />
                    <Image
                      src={product.images[0]?.image || "/assets/images/products/placeholder.png"}
                      alt={product.name}
                      fill
                      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                      className="relative z-10 object-contain p-4 lg:p-6 group-hover:scale-105 transition-transform duration-500"
                    />
                  </Link>

                  <div className="p-6 flex-1 flex flex-col">
                    <span className="text-xs font-semibold text-green-600 uppercase tracking-wider mb-2">
                      {product.category.name}
                    </span>
                    <h3 className="text-lg font-bold text-[#0a1628] mb-2">
                      <Link href={`/products/${product.slug}`} className="hover:text-green-600 transition-colors">
                        {product.name}
                      </Link>
                    </h3>
                    <p className="text-sm text-[#64748b] mb-4 flex-1 line-clamp-2 leading-relaxed">
                      {product.short_description}
                    </p>
                    <p className="text-xl font-extrabold text-[#0d3b66] mb-4">
                      {formatPrice(Number(product.price))}
                    </p>
                    <div className="flex gap-3">
                      <Link
                        href={`/products/${product.slug}`}
                        className="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-full font-semibold text-sm bg-white border border-[#e2e8f0] text-[#0a1628] hover:bg-[#f8fafc] hover:border-[#4a90d9] hover:text-[#0d3b66] transition-colors"
                      >
                        View Details
                      </Link>
                      <QuickAddButton product={product} disabled={product.stock_quantity <= 0} />
                    </div>
                  </div>
                </motion.article>
              ))}
            </div>
          )}

          {/* Empty state */}
          {!isLoading && !hasError && filteredProducts.length === 0 && (
            <EmptyProducts />
          )}
        </Container>
      </section>
    </main>
  );
}
