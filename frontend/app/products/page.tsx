"use client";

import { useState, useMemo, useEffect, useCallback, type ReactNode } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { Search, ChevronLeft, ChevronRight, FileText, ArrowRight, X, Check } from "lucide-react";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { whyChoosePillars } from "@/components/sections/why-choose-section";
import { EmptyProducts } from "@/components/ui/empty-products";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { useProducts } from "@/hooks/use-products";
import { useCategories } from "@/hooks/use-categories";
import { cn } from "@/lib/utils";
import type { Product } from "@/types";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { Button } from "@/components/ui/button";

const sortOptions = [
  { value: "", label: "Featured" },
  { value: "newest", label: "Newest" },
  { value: "name_asc", label: "Name — A-Z" },
  { value: "name_desc", label: "Name — Z-A" },
];

const availabilityOptions = [
  { value: "", label: "All Availability" },
  { value: "in_stock", label: "In Stock" },
];

const categoryToIndustries: Record<string, string[]> = {
  "laundry-detergents": ["Commercial Laundries", "Hotels", "Hospitals", "Schools"],
  "fabric-care": ["Hotels", "Commercial Laundries", "Households", "Retail"],
  "stain-removal": ["Commercial Laundries", "Cleaning Companies", "Hotels"],
  "garment-finishing": ["Commercial Laundries", "Hotels", "Manufacturers"],
};

function packageSizes(product: Product): string[] {
  if (!product.specifications) return [];
  const raw =
    product.specifications["Package Sizes"] ||
    product.specifications["Sizes"] ||
    product.specifications["Pack Sizes"] ||
    product.specifications["Available Sizes"] ||
    "";
  return raw
    .split(/[,\/]/)
    .map((s) => s.trim())
    .filter(Boolean);
}

function industriesForProduct(product: Product): string[] {
  return categoryToIndustries[product.category.slug] || ["Commercial Use"];
}

function applicationsForProduct(product: Product): string[] {
  return product.features?.slice(0, 3) || industriesForProduct(product);
}

export default function ProductsPage() {
  const prefersReducedMotion = useReducedMotion();
  const router = useRouter();
  const searchParams = useSearchParams();

  const [query, setQuery] = useState(searchParams.get("q") ?? "");
  const [category, setCategory] = useState(searchParams.get("category") ?? "");
  const [packageSize, setPackageSize] = useState(searchParams.get("package_size") ?? "");
  const [industry, setIndustry] = useState(searchParams.get("industry") ?? "");
  const [availability, setAvailability] = useState(searchParams.get("availability") ?? "");
  const [sort, setSort] = useState(searchParams.get("sort") ?? "");
  const [page, setPage] = useState(Number(searchParams.get("page") ?? 1));
  const [compare, setCompare] = useState<string[]>([]);

  const {
    data: allProducts,
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

  const isLoading = productsLoading || categoriesLoading;
  const hasError = productsError || categoriesError;

  const packageSizeOptions = useMemo(() => {
    if (!allProducts) return [{ value: "", label: "All Package Sizes" }];
    const sizes = new Set<string>();
    allProducts.forEach((p) => packageSizes(p).forEach((s) => sizes.add(s)));
    return [{ value: "", label: "All Package Sizes" }, ...Array.from(sizes).sort().map((s) => ({ value: s, label: s }))];
  }, [allProducts]);

  const industryOptions = useMemo(() => {
    if (!categories) return [{ value: "", label: "All Industries" }];
    const industries = new Set<string>();
    categories.forEach((c) => {
      (categoryToIndustries[c.slug] || []).forEach((i) => industries.add(i));
    });
    return [{ value: "", label: "All Industries" }, ...Array.from(industries).sort().map((i) => ({ value: i, label: i }))];
  }, [categories]);

  const filteredProducts = useMemo(() => {
    if (!allProducts) return [];

    let result = allProducts.filter((p) => {
      const matchesSearch =
        !query.trim() ||
        p.name.toLowerCase().includes(query.trim().toLowerCase()) ||
        p.short_description.toLowerCase().includes(query.trim().toLowerCase());
      const matchesCategory = !category || p.category.slug === category;
      const matchesPackageSize = !packageSize || packageSizes(p).includes(packageSize);
      const matchesIndustry = !industry || industriesForProduct(p).includes(industry);
      const matchesAvailability = availability !== "in_stock" || p.stock_quantity > 0 || p.status === "active";
      return matchesSearch && matchesCategory && matchesPackageSize && matchesIndustry && matchesAvailability;
    });

    switch (sort) {
      case "newest":
        result = [...result].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
        break;
      case "name_asc":
        result = [...result].sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "name_desc":
        result = [...result].sort((a, b) => b.name.localeCompare(a.name));
        break;
      default:
        result = [...result].sort((a, b) => (b.featured ? 1 : 0) - (a.featured ? 1 : 0));
    }

    return result;
  }, [allProducts, query, category, packageSize, industry, availability, sort]);

  const perPage = 12;
  const totalPages = Math.max(1, Math.ceil(filteredProducts.length / perPage));
  const paginatedProducts = useMemo(() => {
    const start = (page - 1) * perPage;
    return filteredProducts.slice(start, start + perPage);
  }, [filteredProducts, page]);

  useEffect(() => {
    setCategory(searchParams.get("category") ?? "");
    setQuery(searchParams.get("q") ?? "");
    setPackageSize(searchParams.get("package_size") ?? "");
    setIndustry(searchParams.get("industry") ?? "");
    setAvailability(searchParams.get("availability") ?? "");
    setSort(searchParams.get("sort") ?? "");
    setPage(Number(searchParams.get("page") ?? 1));
  }, [searchParams]);

  useEffect(() => {
    setPage(1);
  }, [query, category, packageSize, industry, availability, sort]);

  const updateUrl = useCallback(() => {
    const params = new URLSearchParams();
    if (query.trim()) params.set("q", query.trim());
    if (category) params.set("category", category);
    if (packageSize) params.set("package_size", packageSize);
    if (industry) params.set("industry", industry);
    if (availability) params.set("availability", availability);
    if (sort) params.set("sort", sort);
    if (page > 1) params.set("page", String(page));
    const qs = params.toString();
    router.replace(`/products${qs ? `?${qs}` : ""}`, { scroll: false });
  }, [router, page, query, category, packageSize, industry, availability, sort]);

  useEffect(() => {
    updateUrl();
  }, [updateUrl]);

  const resetFilters = useCallback(() => {
    setQuery("");
    setCategory("");
    setPackageSize("");
    setIndustry("");
    setAvailability("");
    setSort("");
    setPage(1);
  }, []);

  const toggleCompare = (slug: string) => {
    setCompare((prev) => {
      if (prev.includes(slug)) return prev.filter((s) => s !== slug);
      if (prev.length >= 3) return prev;
      return [...prev, slug];
    });
  };

  const compareProducts = useMemo(
    () => allProducts?.filter((p) => compare.includes(p.slug)) || [],
    [allProducts, compare]
  );

  const activeCategory = useMemo(
    () => categories?.find((cat) => cat.slug === category) ?? null,
    [categories, category]
  );

  const showCategoryBrowse = !category;

  useEffect(() => {
    if (!category) return;
    const target = document.getElementById("products-listing");
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }, [category]);

  const activeFiltersCount = [category, packageSize, industry, availability, sort].filter(Boolean).length;

  return (
    <main>
      <PageHero
        title={activeCategory ? activeCategory.name : "Professional Cleaning Products"}
        subtitle={
          activeCategory?.description ||
          "Explore our range of professional detergents and fabric care solutions manufactured for businesses, institutions, and distributors."
        }
        breadcrumb={
          activeCategory
            ? [{ label: "Products", href: "/products" }, { label: activeCategory.name }]
            : [{ label: "Products" }]
        }
      />

      {/* Categories — browse view only */}
      {showCategoryBrowse && (
      <section className="py-16 lg:py-20 bg-white" aria-labelledby="categories-heading">
        <Container>
          <SectionHeader
            id="categories-heading"
            title="Product Categories"
            subtitle="Solutions developed for every professional cleaning need."
          />
          {categoriesLoading ? (
            <SkeletonGrid count={4} />
          ) : categoriesError ? (
            <ApiError message="Failed to load categories." onRetry={refetchCategories} />
          ) : (
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {categories?.map((cat, index) => (
                <motion.div
                  key={cat.id}
                  initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.5, delay: index * 0.08 }}
                >
                  <Link
                    href={`/products?category=${encodeURIComponent(cat.slug)}`}
                    className={cn(
                      "block w-full text-left p-6 rounded-[20px] border transition-all-base h-full",
                      "border-default bg-white hover:-translate-y-1 hover:shadow-md hover:border-primary-300"
                    )}
                  >
                    <div className="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center text-primary-500 mb-4">
                      <span className="text-xl font-bold">{cat.name.charAt(0)}</span>
                    </div>
                    <h3 className="text-lg font-bold text-text-heading mb-1">{cat.name}</h3>
                    <p className="text-sm text-text-muted line-clamp-2">{cat.description || `Explore ${cat.name}.`}</p>
                  </Link>
                </motion.div>
              ))}
            </div>
          )}
        </Container>
      </section>
      )}

      {/* Filter Bar */}
      <section className="py-6 bg-surface-page border-y border-default" aria-label="Product filters">
        <Container>
          <div className="flex flex-col lg:flex-row gap-4 lg:items-end">
            <div className="relative flex-1 max-w-md">
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
                className="w-full pl-12 pr-4 py-3 rounded-full border border-default bg-white text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-transparent transition-all-base"
              />
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 flex-1">
              <FilterSelect
                id="category-filter"
                label="Category"
                value={category}
                onChange={setCategory}
                options={[{ value: "", label: "All Categories" }, ...(categories?.map((c) => ({ value: c.slug, label: c.name })) || [])]}
              />
              <FilterSelect
                id="package-size-filter"
                label="Package Size"
                value={packageSize}
                onChange={setPackageSize}
                options={packageSizeOptions}
              />
              <FilterSelect
                id="industry-filter"
                label="Industry"
                value={industry}
                onChange={setIndustry}
                options={industryOptions}
              />
              <FilterSelect
                id="availability-filter"
                label="Availability"
                value={availability}
                onChange={setAvailability}
                options={availabilityOptions}
              />
              <FilterSelect
                id="sort-filter"
                label="Sort"
                value={sort}
                onChange={setSort}
                options={sortOptions}
              />
            </div>
          </div>

          {activeFiltersCount > 0 && (
            <div className="flex items-center gap-3 mt-4">
              <span className="text-sm text-text-muted">{filteredProducts.length} result(s)</span>
              <button
                type="button"
                onClick={resetFilters}
                className="text-sm font-semibold text-secondary-600 hover:text-secondary-700"
              >
                Reset filters
              </button>
            </div>
          )}
        </Container>
      </section>

      {/* Compare Banner */}
      {compareProducts.length > 0 && (
        <section className="py-4 bg-primary-900 text-white" aria-label="Product comparison selection">
          <Container>
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
              <p className="text-sm">
                Comparing {compareProducts.length} of 3 products
              </p>
              <button
                type="button"
                onClick={() => setCompare([])}
                className="text-sm font-semibold text-white/80 hover:text-white"
              >
                Clear comparison
              </button>
            </div>
          </Container>
        </section>
      )}

      {/* Products Grid */}
      <section id="products-listing" className="py-16 lg:py-24 bg-white scroll-mt-28" aria-labelledby="products-heading">
        <Container>
          {activeCategory && (
            <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
              <div>
                <p className="text-sm font-semibold uppercase tracking-wider text-secondary-600 mb-1">Category</p>
                <h2 className="text-2xl sm:text-3xl font-extrabold text-text-heading tracking-tight">
                  {activeCategory.name}
                </h2>
              </div>
              <Link
                href="/products"
                className="text-sm font-semibold text-secondary-600 hover:text-secondary-700"
              >
                View all categories
              </Link>
            </div>
          )}
          <h2 id="products-heading" className="sr-only">
            Product Listing
          </h2>

          {hasError && (
            <ApiError
              message="Failed to load products. Please try again."
              onRetry={() => {
                refetchProducts();
                refetchCategories();
              }}
            />
          )}

          {isLoading && !hasError && <SkeletonGrid count={6} />}

          {!isLoading && !hasError && paginatedProducts.length > 0 && (
            <>
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                {paginatedProducts.map((product, index) => (
                  <ProductCard
                    key={product.id}
                    product={product}
                    index={index}
                    isCompared={compare.includes(product.slug)}
                    onToggleCompare={() => toggleCompare(product.slug)}
                    compareDisabled={compare.length >= 3 && !compare.includes(product.slug)}
                  />
                ))}
              </div>

              {totalPages > 1 && (
                <div className="flex items-center justify-center gap-2 mt-10">
                  <button
                    type="button"
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page <= 1}
                    className="p-2 rounded-xl border border-default bg-white text-text-heading hover:bg-surface-page disabled:opacity-50"
                    aria-label="Previous page"
                  >
                    <ChevronLeft className="w-5 h-5" aria-hidden="true" />
                  </button>
                  {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                    <button
                      key={p}
                      type="button"
                      onClick={() => setPage(p)}
                      className={cn(
                        "w-10 h-10 rounded-xl text-sm font-semibold border transition-colors-base",
                        page === p
                          ? "bg-secondary-500 text-white border-secondary-500"
                          : "bg-white text-text-heading border-default hover:bg-surface-page"
                      )}
                      aria-current={page === p ? "page" : undefined}
                    >
                      {p}
                    </button>
                  ))}
                  <button
                    type="button"
                    onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                    disabled={page >= totalPages}
                    className="p-2 rounded-xl border border-default bg-white text-text-heading hover:bg-surface-page disabled:opacity-50"
                    aria-label="Next page"
                  >
                    <ChevronRight className="w-5 h-5" aria-hidden="true" />
                  </button>
                </div>
              )}
            </>
          )}

          {!isLoading && !hasError && paginatedProducts.length === 0 && (
            <EmptyProducts onReset={resetFilters} />
          )}
        </Container>
      </section>

      {/* Comparison Table */}
      {compareProducts.length > 1 && (
        <section className="py-16 lg:py-24 bg-surface-page" aria-labelledby="compare-heading">
          <Container>
            <SectionHeader
              id="compare-heading"
              title="Compare Products"
              subtitle="Side-by-side comparison of selected VESTRA® solutions."
            />
            <div className="overflow-x-auto">
              <table className="w-full text-sm border-collapse">
                <thead>
                  <tr>
                    <th className="text-left p-4 bg-white border border-default min-w-[160px]">Attribute</th>
                    {compareProducts.map((p) => (
                      <th key={p.id} className="text-left p-4 bg-white border border-default min-w-[200px]">
                        <div className="flex items-center justify-between gap-2">
                          <span className="font-bold text-text-heading">{p.name}</span>
                          <button
                            type="button"
                            onClick={() => toggleCompare(p.slug)}
                            className="text-text-muted hover:text-danger-500"
                            aria-label={`Remove ${p.name} from comparison`}
                          >
                            <X className="w-4 h-4" />
                          </button>
                        </div>
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td className="p-4 border border-default font-semibold text-text-heading bg-surface-page">Applications</td>
                    {compareProducts.map((p) => (
                      <td key={p.id} className="p-4 border border-default align-top">
                        {applicationsForProduct(p).join(", ")}
                      </td>
                    ))}
                  </tr>
                  <tr>
                    <td className="p-4 border border-default font-semibold text-text-heading bg-surface-page">Industries</td>
                    {compareProducts.map((p) => (
                      <td key={p.id} className="p-4 border border-default align-top">
                        {industriesForProduct(p).join(", ")}
                      </td>
                    ))}
                  </tr>
                  <tr>
                    <td className="p-4 border border-default font-semibold text-text-heading bg-surface-page">Package Sizes</td>
                    {compareProducts.map((p) => (
                      <td key={p.id} className="p-4 border border-default align-top">
                        {packageSizes(p).join(", ") || "—"}
                      </td>
                    ))}
                  </tr>
                  <tr>
                    <td className="p-4 border border-default font-semibold text-text-heading bg-surface-page">Key Benefits</td>
                    {compareProducts.map((p) => (
                      <td key={p.id} className="p-4 border border-default align-top">
                        {p.benefits?.slice(0, 3).join("; ") || "—"}
                      </td>
                    ))}
                  </tr>
                </tbody>
              </table>
            </div>
          </Container>
        </section>
      )}

      {/* Why Choose */}
      <section className="py-16 lg:py-24 bg-primary-900" aria-labelledby="why-choose-heading">
        <Container>
          <SectionHeader
            id="why-choose-heading"
            title="Why Choose VESTRA®"
            subtitle="Different cleaning challenges require different solutions."
            light
          />
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4 lg:gap-5">
            {whyChoosePillars.map((pillar, index) => (
              <motion.article
                key={pillar.number}
                initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.5, delay: index * 0.08 }}
                className="rounded-[20px] border border-white/10 bg-white/5 p-5 text-white sm:p-6"
              >
                <p className="mb-3 font-mono text-sm font-bold tracking-[0.18em] text-secondary-500">
                  {pillar.number}
                </p>
                <h3 className="mb-2 text-lg font-bold uppercase tracking-wide">{pillar.title}</h3>
                <p className="text-sm leading-relaxed text-white/75">{pillar.description}</p>
              </motion.article>
            ))}
          </div>
          <p className="mt-8 text-center text-base font-semibold tracking-tight text-white sm:text-xl">
            Clean deeper. Care smarter. Finish better.
          </p>
        </Container>
      </section>

      {/* CTA */}
      <section className="py-20 lg:py-28 bg-white" aria-labelledby="help-heading">
        <Container>
          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7 }}
            className="max-w-3xl mx-auto text-center px-6 py-12 lg:px-12 lg:py-16 rounded-[28px] border border-default shadow-lg bg-surface-card"
          >
            <h2 id="help-heading" className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-text-heading mb-4 tracking-tight">
              Need Help Choosing?
            </h2>
            <p className="text-base lg:text-lg text-text-muted mb-8 leading-relaxed">
              Our team can recommend the right products and package sizes for your business or institution.
            </p>
            <div className="flex flex-wrap justify-center gap-4">
              <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto group" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
              <Link href="/request-quote" data-track="products-cta-quote">Request a Quote</Link>
            </Button>
              <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto">
                <Link href="/contact" data-track="products-cta-contact">Contact Sales</Link>
              </Button>
              <Button asChild variant="link" className="rounded-full px-7 py-3.5 h-auto">
                <Link href="/distributor" data-track="products-cta-distributor">Become a Distributor</Link>
              </Button>
            </div>
          </motion.div>
        </Container>
      </section>
    </main>
  );
}

function FilterSelect({
  id,
  label,
  value,
  onChange,
  options,
}: {
  id: string;
  label: string;
  value: string;
  onChange: (value: string) => void;
  options: { value: string; label: string }[];
}) {
  return (
    <div className="flex flex-col gap-1.5">
      <label htmlFor={id} className="text-xs font-semibold text-text-muted uppercase tracking-wider">
        {label}
      </label>
      <select
        id={id}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="px-3 py-2.5 rounded-xl text-sm font-medium border border-default bg-white text-text-heading focus:outline-none focus:ring-2 focus:ring-secondary-500"
      >
        {options.map((opt) => (
          <option key={opt.value} value={opt.value}>
            {opt.label}
          </option>
        ))}
      </select>
    </div>
  );
}

function ProductCard({
  product,
  index,
  isCompared,
  onToggleCompare,
  compareDisabled,
}: {
  product: Product;
  index: number;
  isCompared: boolean;
  onToggleCompare: () => void;
  compareDisabled: boolean;
}) {
  const prefersReducedMotion = useReducedMotion();
  const sizes = packageSizes(product);
  const industries = industriesForProduct(product);
  const features = product.features?.slice(0, 3) || [];

  return (
    <motion.article
      initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-100px" }}
      transition={{ duration: 0.5, delay: (index % 6) * 0.08 }}
      className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-primary-300 transition-all-base flex flex-col"
    >
      <div className="relative p-6 lg:p-8 min-h-[220px] lg:min-h-[240px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white overflow-hidden">
        <div className="absolute top-3 right-3 z-20">
          <button
            type="button"
            onClick={onToggleCompare}
            disabled={compareDisabled}
            className={cn(
              "flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors-base",
              isCompared
                ? "bg-secondary-500 text-white border-secondary-500"
                : "bg-white/90 text-text-heading border-default hover:border-secondary-500 disabled:opacity-40"
            )}
            aria-pressed={isCompared}
          >
            {isCompared ? <Check className="w-3.5 h-3.5" /> : <span className="w-3.5 h-3.5 rounded-full border border-current" />}
            {isCompared ? "Comparing" : "Compare"}
          </button>
        </div>
        <div className="absolute w-44 h-44 rounded-full bg-[radial-gradient(circle,rgba(13,59,102,0.05)_0%,transparent_70%)]" />
        <Image
          src={product.images[0]?.image || "/assets/images/products/placeholder.png"}
          alt={product.name}
          fill
          sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
          className="relative z-10 object-contain p-4 lg:p-6 group-hover:scale-105 transition-transform duration-500"
        />
      </div>

      <div className="p-6 flex-1 flex flex-col">
        <span className="text-xs font-semibold text-secondary-600 uppercase tracking-wider mb-2">
          {product.category.name}
        </span>
        <h3 className="text-lg font-bold text-text-heading mb-2">
          <Link href={`/products/${product.slug}`} className="hover:text-secondary-600 transition-colors-base">
            {product.name}
          </Link>
        </h3>
        <p className="text-sm text-text-muted mb-4 line-clamp-2 leading-relaxed">
          {product.short_description}
        </p>

        {sizes.length > 0 && (
          <div className="flex flex-wrap gap-1.5 mb-3">
            {sizes.map((size) => (
              <span key={size} className="px-2.5 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                {size}
              </span>
            ))}
          </div>
        )}

        {industries.length > 0 && (
          <div className="flex flex-wrap gap-1.5 mb-3">
            {industries.slice(0, 2).map((industry) => (
              <span key={industry} className="px-2.5 py-1 rounded-full text-xs font-medium bg-secondary-50 text-secondary-700">
                {industry}
              </span>
            ))}
          </div>
        )}

        {features.length > 0 && (
          <ul className="space-y-1.5 mb-5">
            {features.map((feature) => (
              <li key={feature} className="flex items-start gap-2 text-xs text-text-muted">
                <Check className="w-3.5 h-3.5 text-secondary-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <span className="line-clamp-1">{feature}</span>
              </li>
            ))}
          </ul>
        )}

        <div className="flex flex-col sm:flex-row gap-3 mt-auto">
          <Button asChild variant="outline" className="w-full sm:flex-1 rounded-full px-4 py-2.5 h-auto text-sm">
            <Link href={`/products/${product.slug}`}>Learn More</Link>
          </Button>
          <Button asChild variant="default" className="w-full sm:flex-1 rounded-full px-4 py-2.5 h-auto text-sm" leftIcon={<FileText className="w-4 h-4" aria-hidden="true" />}>
            <Link href={`/request-quote?product=${encodeURIComponent(product.slug)}`} data-track="product-card-quote">Request Quote</Link>
          </Button>
        </div>
      </div>
    </motion.article>
  );
}
