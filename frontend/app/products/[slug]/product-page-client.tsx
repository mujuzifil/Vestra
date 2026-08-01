"use client";

import { useState, useEffect, useRef } from "react";
import { notFound } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { Check, Package, ArrowRight, FileText } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ProductGallery } from "@/components/common/product-gallery";
import { CTASection } from "@/components/common/cta-section";
import { ApiError } from "@/components/ui/api-error";
import { useProduct } from "@/hooks/use-products";
import { useProductRecommendations } from "@/hooks/use-recommendations";
import { useAuth } from "@/lib/auth-context";
import { useRecordProductView } from "@/hooks/use-recently-viewed";
import { JsonLd, productSchema, breadcrumbSchema } from "@/lib/structured-data";
import type { Product } from "@/types";

interface ProductPageClientProps {
  slug: string;
}

const RECENTLY_VIEWED_KEY = "vestra_recently_viewed";
const MAX_RECENT = 4;

const categoryToIndustries: Record<string, string[]> = {
  "laundry-detergents": ["Commercial Laundries", "Hotels", "Hospitals", "Schools"],
  "fabric-care": ["Hotels", "Commercial Laundries", "Households", "Retail"],
  "stain-removal": ["Commercial Laundries", "Cleaning Companies", "Hotels"],
  "garment-finishing": ["Commercial Laundries", "Hotels", "Manufacturers"],
};

interface RecentProduct {
  id: number;
  name: string;
  slug: string;
  image: string;
  category: string;
}

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
  return product.features?.slice(0, 4) || industriesForProduct(product);
}

function usageInstructions(product: Product): string {
  if (!product.specifications) return "";
  return (
    product.specifications["Usage Instructions"] ||
    product.specifications["Directions"] ||
    product.specifications["How to Use"] ||
    ""
  );
}

function getRecentlyViewed(currentSlug: string): RecentProduct[] {
  if (typeof window === "undefined") return [];
  try {
    const raw = localStorage.getItem(RECENTLY_VIEWED_KEY);
    const items: RecentProduct[] = raw ? JSON.parse(raw) : [];
    return items.filter((item) => item.slug !== currentSlug).slice(0, MAX_RECENT);
  } catch {
    return [];
  }
}

function saveToRecentlyViewed(product: RecentProduct) {
  if (typeof window === "undefined") return;
  try {
    const raw = localStorage.getItem(RECENTLY_VIEWED_KEY);
    const items: RecentProduct[] = raw ? JSON.parse(raw) : [];
    const filtered = items.filter((item) => item.slug !== product.slug);
    const updated = [product, ...filtered].slice(0, MAX_RECENT);
    localStorage.setItem(RECENTLY_VIEWED_KEY, JSON.stringify(updated));
  } catch {
    // ignore storage errors
  }
}

export default function ProductPageClient({ slug }: ProductPageClientProps) {
  const {
    data: product,
    isLoading,
    error,
    refetch,
  } = useProduct(slug);

  const { data: recommendations } = useProductRecommendations(slug);
  const { isAuthenticated } = useAuth();

  const [recentlyViewed, setRecentlyViewed] = useState<RecentProduct[]>([]);
  const recordView = useRecordProductView();
  const viewRecordedRef = useRef(false);

  useEffect(() => {
    setRecentlyViewed(getRecentlyViewed(slug));
  }, [slug]);

  useEffect(() => {
    if (product) {
      saveToRecentlyViewed({
        id: product.id,
        name: product.name,
        slug: product.slug,
        image: product.images[0]?.image || "/assets/images/products/placeholder.png",
        category: product.category.name,
      });
    }
  }, [product]);

  useEffect(() => {
    if (product && isAuthenticated && !viewRecordedRef.current) {
      viewRecordedRef.current = true;
      recordView.mutate(product.id);
    }
  }, [product, isAuthenticated, recordView]);

  if (isLoading) {
    return (
      <main className="py-16">
        <Container>
          <div className="grid lg:grid-cols-2 gap-12">
            <div className="aspect-square rounded-[20px] bg-surface-page animate-pulse" />
            <div className="space-y-4">
              <div className="h-6 w-24 rounded bg-neutral-200" />
              <div className="h-10 w-3/4 rounded bg-neutral-200" />
              <div className="h-24 w-full rounded bg-neutral-200" />
              <div className="h-40 w-full rounded bg-neutral-200" />
            </div>
          </div>
        </Container>
      </main>
    );
  }

  if (error) {
    return (
      <main className="py-16">
        <Container>
          <ApiError message="Failed to load product details. Please try again." onRetry={refetch} />
        </Container>
      </main>
    );
  }

  if (!product) {
    notFound();
  }

  const relatedProducts = recommendations?.related ?? [];
  const sizes = packageSizes(product);
  const industries = industriesForProduct(product);
  const applications = applicationsForProduct(product);
  const instructions = usageInstructions(product);

  const productImages = product.images.length > 0
    ? product.images.map((img) => img.image)
    : ["/assets/images/products/placeholder.png"];

  return (
    <>
      <JsonLd data={productSchema(product)} />
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "Products", url: "https://vestradetergents.com/products" },
          { name: product.name, url: `https://vestradetergents.com/products/${product.slug}` },
        ])}
      />
      <main>
        <PageHero
          title={product.name}
          subtitle={product.short_description}
          breadcrumb={[{ label: "Products", href: "/products" }, { label: product.name }]}
        />

        {/* Overview */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="overview-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
              <ProductGallery images={productImages} productName={product.name} />

              <div>
                <span className="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-secondary-500/10 text-secondary-600 mb-4">
                  {product.category.name}
                </span>
                <h1
                  id="overview-heading"
                  className="text-3xl lg:text-4xl font-extrabold text-primary-900 mb-4 tracking-tight"
                >
                  {product.name}
                </h1>

                <div className="flex flex-wrap items-center gap-3 mb-6">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-neutral-100 text-body">
                    <Package className="w-3.5 h-3.5" aria-hidden="true" />
                    SKU: {product.sku}
                  </span>
                  {product.status === "active" && (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-secondary-500/10 text-secondary-600">
                      <span className="w-2 h-2 rounded-full bg-secondary-500" />
                      Available
                    </span>
                  )}
                </div>

                <p className="text-body text-base lg:text-lg leading-relaxed mb-8">
                  {product.description}
                </p>

                {/* B2B enquiry actions */}
                <div className="flex flex-col sm:flex-row gap-3 mb-6">
                  <Link
                    href={`/request-quote?product=${encodeURIComponent(product.slug)}`}
                    data-track="product-detail-quote"
                    className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-0.5 transition-all-base"
                  >
                    <FileText className="w-4 h-4" aria-hidden="true" />
                    Request a Quote
                  </Link>
                  <Link
                    href={`/contact?subject=${encodeURIComponent(`Enquiry about ${product.name}`)}`}
                    data-track="product-detail-contact"
                    className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-white border border-default hover:bg-surface-page transition-colors-base"
                  >
                    Contact Sales
                  </Link>
                </div>

                <Link
                  href="/distributor"
                  data-track="product-detail-distributor"
                  className="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 hover:text-secondary-700 underline underline-offset-2"
                >
                  Interested in reseller or bulk pricing? Become a distributor
                  <ArrowRight className="w-4 h-4" aria-hidden="true" />
                </Link>
              </div>
            </div>
          </Container>
        </section>

        {/* Benefits & Applications */}
        <section className="py-16 lg:py-24 bg-surface-page" aria-labelledby="benefits-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-20">
              <div>
                <SectionHeader
                  id="benefits-heading"
                  title="Key Benefits"
                  subtitle="Why businesses choose this product."
                  centered={false}
                  className="mb-8"
                />
                {product.benefits && product.benefits.length > 0 ? (
                  <ul className="space-y-3">
                    {product.benefits.map((benefit) => (
                      <li key={benefit} className="flex items-start gap-3 text-body">
                        <Check className="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                        {benefit}
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="text-body">Benefits information coming soon.</p>
                )}
              </div>

              <div>
                <SectionHeader
                  title="Applications"
                  subtitle="Typical uses for this solution."
                  centered={false}
                  className="mb-8"
                />
                <div className="flex flex-wrap gap-2">
                  {applications.map((app) => (
                    <span
                      key={app}
                      className="px-4 py-2 rounded-full text-sm font-medium bg-white border border-default text-body"
                    >
                      {app}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          </Container>
        </section>

        {/* Package Sizes & Usage */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="sizes-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-20">
              <div>
                <SectionHeader
                  id="sizes-heading"
                  title="Available Package Sizes"
                  subtitle="Choose the format that fits your operation."
                  centered={false}
                  className="mb-8"
                />
                {sizes.length > 0 ? (
                  <div className="flex flex-wrap gap-3">
                    {sizes.map((size) => (
                      <span
                        key={size}
                        className="px-5 py-2.5 rounded-full text-sm font-semibold bg-primary-50 text-primary-700 border border-primary-100"
                      >
                        {size}
                      </span>
                    ))}
                  </div>
                ) : (
                  <p className="text-body">Package size information coming soon.</p>
                )}
              </div>

              <div>
                <SectionHeader
                  title="Usage Instructions"
                  subtitle="Recommended guidance for best results."
                  centered={false}
                  className="mb-8"
                />
                {instructions ? (
                  <p className="text-body leading-relaxed">{instructions}</p>
                ) : (
                  <p className="text-body">Usage instructions coming soon.</p>
                )}
              </div>
            </div>
          </Container>
        </section>

        {/* Industries */}
        <section className="py-16 lg:py-24 bg-primary-900" aria-labelledby="industries-heading">
          <Container>
            <SectionHeader
              id="industries-heading"
              title="Industries Served"
              subtitle="Sectors that rely on this product."
              light
            />
            <div className="flex flex-wrap justify-center gap-3">
              {industries.map((industry) => (
                <span
                  key={industry}
                  className="px-5 py-2.5 rounded-full text-sm font-semibold bg-white/10 text-white border border-white/10"
                >
                  {industry}
                </span>
              ))}
            </div>
          </Container>
        </section>

        {/* Specifications */}
        {product.specifications && Object.keys(product.specifications).length > 0 && (
          <section className="py-16 lg:py-24 bg-surface-page" aria-labelledby="specs-heading">
            <Container>
              <SectionHeader
                id="specs-heading"
                title="Product Specifications"
                subtitle="Detailed technical information."
              />
              <div className="max-w-3xl mx-auto rounded-[16px] border border-default overflow-hidden bg-white">
                <table className="w-full text-sm">
                  <tbody>
                    {Object.entries(product.specifications)
                      .filter(([key]) => !["Package Sizes", "Sizes", "Pack Sizes", "Usage Instructions", "Directions"].includes(key))
                      .map(([key, value]) => (
                        <tr key={key} className="border-b border-default last:border-0">
                          <td className="px-5 py-3 font-semibold text-primary-900 bg-surface-page w-1/3">
                            {key}
                          </td>
                          <td className="px-5 py-3 text-body">{value}</td>
                        </tr>
                      ))}
                  </tbody>
                </table>
              </div>
            </Container>
          </section>
        )}

        {/* Recently Viewed */}
        {recentlyViewed.length > 0 && (
          <section className="py-16 lg:py-24 bg-white" aria-labelledby="recently-viewed-heading">
            <Container>
              <SectionHeader
                id="recently-viewed-heading"
                title="Recently Viewed"
                subtitle="Products you have browsed recently."
              />
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                {recentlyViewed.map((recent) => (
                  <Link
                    key={recent.id}
                    href={`/products/${recent.slug}`}
                    className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-primary-300 transition-all-base"
                  >
                    <div className="relative p-6 min-h-[180px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white">
                      <Image
                        src={recent.image}
                        alt={recent.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                        className="object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                    <div className="p-5">
                      <span className="text-xs font-semibold text-secondary-600 uppercase tracking-wider">
                        {recent.category}
                      </span>
                      <h3 className="text-base font-bold text-primary-900 mt-1">{recent.name}</h3>
                    </div>
                  </Link>
                ))}
              </div>
            </Container>
          </section>
        )}

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <section className="py-16 lg:py-24 bg-surface-page" aria-labelledby="related-heading">
            <Container>
              <SectionHeader
                id="related-heading"
                title="Related Products"
                subtitle="More solutions from the same category."
              />
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                {relatedProducts.map((related) => (
                  <Link
                    key={related.id}
                    href={`/products/${related.slug}`}
                    className="group bg-white rounded-[20px] overflow-hidden border border-default shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-primary-300 transition-all-base"
                  >
                    <div className="relative p-6 min-h-[200px] flex items-center justify-center bg-gradient-to-b from-neutral-50 to-white">
                      <Image
                        src={related.images[0]?.image || "/assets/images/products/placeholder.png"}
                        alt={related.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                        className="object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                    <div className="p-6">
                      <span className="text-xs font-semibold text-secondary-600 uppercase tracking-wider">
                        {related.category.name}
                      </span>
                      <h3 className="text-lg font-bold text-primary-900 mt-1 mb-2">{related.name}</h3>
                      <p className="text-sm text-muted line-clamp-2">{related.short_description}</p>
                    </div>
                  </Link>
                ))}
              </div>
            </Container>
          </section>
        )}

        <CTASection
          title="Need help selecting the right product?"
          description="Our sales team can recommend the best solution and package size for your business."
          buttonText="Request a Quote"
          buttonHref="/request-quote"
          secondaryButton={{ text: "Become a Distributor", href: "/distributor" }}
          light
        />
      </main>
    </>
  );
}
