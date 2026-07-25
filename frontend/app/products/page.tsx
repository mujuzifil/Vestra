"use client";

import { useState, useMemo, useEffect, useCallback } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { Search, ShoppingCart, Loader2, ChevronLeft, ChevronRight } from "lucide-react";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { EmptyProducts } from "@/components/ui/empty-products";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { useProductSearch } from "@/hooks/use-products";
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
        "inline-flex items-center justify-center w-10 h-10 rounded-full border border-default bg-white text-primary-900 hover:bg-surface-page hover:border-green-500 hover:text-green-600 transition-colors-base",
        (disabled || loading) && "opacity-60 cursor-not-allowed"
      )}
      aria-label="Add to cart"
    >
      {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShoppingCart className="w-4 h-4" />}
    </button>
  );
}

const sortOptions = [
  { value: "", label: "Featured" },
  { value: "newest", label: "Newest" },
  { value: "price_asc", label: "Price — Low to High" },
  { value: "price_desc", label: "Price — High to Low" },
  { value: "name_asc", label: "Name — A-Z" },
  { value: "name_desc", label: "Name — Z-A" },
];

export default function ProductsPage() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [query, setQuery] = useState(searchParams.get("q") ?? "");
  const [activeCategory, setActiveCategory] = useState(searchParams.get("category") ?? "all");
  const [sort, setSort] = useState(searchParams.get("sort") ?? "");
  const [page, setPage] = useState(Number(searchParams.get("page") ?? 1));

  const filters = useMemo(
    () => ({
      page,
      per_page: 12,
      search: query.trim() || undefined,
      category: activeCategory === "all" ? undefined : activeCategory,
      sort: sort || undefined,
    }),
    [page, query, activeCategory, sort]
  );

  const {
    data: searchResult,
    isLoading: productsLoading,
    error: productsError,
    refetch: refetchProducts,
  } = useProductSearch(filters);

  const {
    data: categories,
    isLoading: categoriesLoading,
    error: categoriesError,
    refetch: refetchCategories,
  } = useCategories();

  const allCategories = useMemo(() => {
    if (!categories) return [{ id: 0, name: "All Products", slug: "all" }];
    return [{ id: 0, name: "All Products", slug: "all" }, ...categories];
  }, [categories]);

  useEffect(() => {
    setPage(1);
  }, [query, activeCategory, sort]);

  const updateUrl = useCallback(() => {
    const params = new URLSearchParams();
    if (query.trim()) params.set("q", query.trim());
    if (activeCategory !== "all") params.set("category", activeCategory);
    if (sort) params.set("sort", sort);
    if (page > 1) params.set("page", String(page));
    const qs = params.toString();
    router.replace(`/products${qs ? `?${qs}` : ""}`, { scroll: false });
  }, [router, page, query, activeCategory, sort]);

  useEffect(() => {
    updateUrl();
  }, [updateUrl]);

  const isLoading = productsLoading || categoriesLoading;
  const hasError = productsError || categoriesError;
  const products = searchResult?.data ?? [];
  const meta = searchResult?.meta;

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

          {/* Search and sort */}
          <div className="flex flex-col lg:flex-row gap-4 lg:items-center justify-between mb-10">
            <div className="relative max-w-md w-full">
              <label htmlFor="product-search" className="sr-only">
                Search products
              </label>
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-placeholder" aria-hidden="true" />
              <input
                id="product-search"
                type="text"
                placeholder="Search products..."
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                className="w-full pl-12 pr-4 py-3 rounded-full border border-default bg-surface-page text-primary-900 placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all-base"
              />
            </div>

            <div className="flex items-center gap-3">
              <label htmlFor="sort-select" className="text-sm font-semibold text-primary-900">
                Filter:
              </label>
              <select
                id="sort-select"
                value={sort}
                onChange={(e) => setSort(e.target.value)}
                className="px-4 py-2.5 rounded-full text-sm font-semibold border border-default bg-white text-primary-900 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                {sortOptions.map((option) => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Category tabs */}
          <div className="flex flex-wrap gap-2 mb-10" role="tablist" aria-label="Product categories">
            {categoriesLoading ? (
              <div className="flex gap-2">
                {Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="w-24 h-9 rounded-full bg-neutral-200 animate-pulse" />
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
                    "px-4 py-2 rounded-full text-sm font-semibold transition-all-base",
                    activeCategory === category.slug
                      ? "bg-green-500 text-white shadow-md shadow-green-500/25"
                      : "bg-neutral-100 text-body hover:bg-neutral-200"
                  )}
                >
                  {category.name}
                </button>
              ))
            )}
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
          {!isLoading && !hasError && products.length > 0 && (
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
              {products.map((product, index) => (
                <motion.article
                  key={product.id}
                  initial={{ opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.5, delay: (index % 6) * 0.08 }}
                  className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-primary-300 transition-all-base flex flex-col"
                >
                  <Link
                    href={`/products/${product.slug}`}
                    className="relative p-6 lg:p-8 min-h-[240px] lg:min-h-[260px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white overflow-hidden"
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
                    <h3 className="text-lg font-bold text-primary-900 mb-2">
                      <Link href={`/products/${product.slug}`} className="hover:text-green-600 transition-colors-base">
                        {product.name}
                      </Link>
                    </h3>
                    <p className="text-sm text-muted mb-4 flex-1 line-clamp-2 leading-relaxed">
                      {product.short_description}
                    </p>
                    <p className="text-xl font-extrabold text-primary-500 mb-4">{formatPrice(Number(product.price))}</p>
                    <div className="flex gap-3">
                      <Link
                        href={`/products/${product.slug}`}
                        className="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-full font-semibold text-sm bg-white border border-default text-primary-900 hover:bg-surface-page hover:border-primary-400 hover:text-primary-500 transition-colors-base"
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
          {!isLoading && !hasError && products.length === 0 && <EmptyProducts />}

          {/* Pagination */}
          {!isLoading && !hasError && meta && meta.last_page > 1 && (
            <div className="flex items-center justify-center gap-2 mt-10">
              <button
                type="button"
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page <= 1}
                className="p-2 rounded-xl border border-default bg-white text-primary-900 hover:bg-surface-page disabled:opacity-50"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>
              {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
                <button
                  key={p}
                  type="button"
                  onClick={() => setPage(p)}
                  className={cn(
                    "w-10 h-10 rounded-xl text-sm font-semibold border transition-colors-base",
                    page === p
                      ? "bg-green-500 text-white border-green-500"
                      : "bg-white text-primary-900 border-default hover:bg-surface-page"
                  )}
                >
                  {p}
                </button>
              ))}
              <button
                type="button"
                onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                disabled={page >= meta.last_page}
                className="p-2 rounded-xl border border-default bg-white text-primary-900 hover:bg-surface-page disabled:opacity-50"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          )}
        </Container>
      </section>
    </main>
  );
}
