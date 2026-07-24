"use client";

import React, { useState, useEffect } from "react";
import { notFound, useRouter } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import {
  Check,
  Minus,
  Plus,
  ShoppingCart,
  Zap,
  Package,
  Loader2,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ProductGallery } from "@/components/common/product-gallery";
import { CTASection } from "@/components/common/cta-section";
import { ApiError } from "@/components/ui/api-error";
import { useProduct } from "@/hooks/use-products";
import { useProducts } from "@/hooks/use-products";
import { useCartContext, toCartProduct } from "@/lib/cart-context";
import { toastAddedToCart } from "@/lib/toast-utils";
import { formatPrice, cn } from "@/lib/utils";
import { ReviewList } from "@/components/reviews/review-list";
import { ReviewForm } from "@/components/reviews/review-form";
import { useProductReviews, useSubmitReview } from "@/hooks/use-reviews";
import { StarRating } from "@/components/reviews/star-rating";
import { JsonLd, productSchema, breadcrumbSchema } from "@/lib/structured-data";

interface ProductPageClientProps {
  slug: string;
}

const RECENTLY_VIEWED_KEY = "vestra_recently_viewed";
const MAX_RECENT = 4;

interface RecentProduct {
  id: number;
  name: string;
  slug: string;
  price: string;
  image: string;
  category: string;
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
  const router = useRouter();
  const {
    data: product,
    isLoading,
    error,
    refetch,
  } = useProduct(slug);

  const { data: allProducts } = useProducts();
  const { data: reviewsData } = useProductReviews(slug);
  const submitReviewMutation = useSubmitReview();
  const { addItem } = useCartContext();

  const [quantity, setQuantity] = useState(1);
  const [adding, setAdding] = useState(false);
  const [buying, setBuying] = useState(false);
  const [recentlyViewed, setRecentlyViewed] = useState<RecentProduct[]>([]);

  useEffect(() => {
    setRecentlyViewed(getRecentlyViewed(slug));
  }, [slug]);

  useEffect(() => {
    if (product) {
      saveToRecentlyViewed({
        id: product.id,
        name: product.name,
        slug: product.slug,
        price: product.price,
        image: product.images[0]?.image || "/assets/images/products/placeholder.png",
        category: product.category.name,
      });
    }
  }, [product]);

  if (isLoading) {
    return (
      <main className="py-16">
        <Container>
          <div className="grid lg:grid-cols-2 gap-12">
            <div className="aspect-square rounded-[20px] bg-[#f8fafc] animate-pulse" />
            <div className="space-y-4">
              <div className="h-6 w-24 rounded bg-[#e2e8f0]" />
              <div className="h-10 w-3/4 rounded bg-[#e2e8f0]" />
              <div className="h-8 w-32 rounded bg-[#e2e8f0]" />
              <div className="h-24 w-full rounded bg-[#e2e8f0]" />
              <div className="h-40 w-full rounded bg-[#e2e8f0]" />
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
          <ApiError
            message="Failed to load product details. Please try again."
            onRetry={refetch}
          />
        </Container>
      </main>
    );
  }

  if (!product) {
    notFound();
  }

  const relatedProducts = (allProducts || [])
    .filter((p) => p.category_id === product.category_id && p.id !== product.id)
    .slice(0, 3);

  const productImages = product.images.length > 0
    ? product.images.map((img) => img.image)
    : ["/assets/images/products/placeholder.png"];

  return (
    <>
      <JsonLd data={productSchema(product)} />
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestra.com/" },
          { name: "Products", url: "https://vestra.com/products" },
          { name: product.name, url: `https://vestra.com/products/${product.slug}` },
        ])}
      />
      <main>
        <PageHero
          title={product.name}
          subtitle={product.short_description}
          breadcrumb={[{ label: "Products", href: "/products" }, { label: product.name }]}
        />

        <section className="py-16 lg:py-24 bg-white">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-start">
              <ProductGallery images={productImages} productName={product.name} />

              <div>
                <span className="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-green-500/10 text-green-600 mb-4">
                  {product.category.name}
                </span>
                <h1 className="text-3xl lg:text-4xl font-extrabold text-[#0a1628] mb-4 tracking-tight">
                  {product.name}
                </h1>
                <p className="text-2xl lg:text-3xl font-extrabold text-[#0d3b66] mb-4">
                  {formatPrice(Number(product.price))}
                </p>

                <div className="flex flex-wrap items-center gap-3 mb-6">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#f1f5f9] text-[#475569]">
                    <Package className="w-3.5 h-3.5" />
                    SKU: {product.sku}
                  </span>
                  <span
                    className={cn(
                      "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold",
                      product.stock_quantity > 0
                        ? "bg-green-500/10 text-green-600"
                        : "bg-red-500/10 text-red-600"
                    )}
                  >
                    <span className={cn("w-2 h-2 rounded-full", product.stock_quantity > 0 ? "bg-green-500" : "bg-red-500")} />
                    {product.stock_quantity > 0 ? `In Stock (${product.stock_quantity})` : "Out of Stock"}
                  </span>
                </div>

                <p className="text-[#475569] text-base lg:text-lg leading-relaxed mb-8">
                  {product.description}
                </p>

                {product.benefits && product.benefits.length > 0 && (
                  <div className="mb-8">
                    <h3 className="text-lg font-bold text-[#0a1628] mb-3">Benefits</h3>
                    <ul className="space-y-2">
                      {product.benefits.map((benefit) => (
                        <li key={benefit} className="flex items-start gap-3 text-[#475569]">
                          <Check className="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                          {benefit}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}

                {product.features && product.features.length > 0 && (
                  <div className="mb-8">
                    <h3 className="text-lg font-bold text-[#0a1628] mb-3">Key Features</h3>
                    <div className="flex flex-wrap gap-2">
                      {product.features.map((feature) => (
                        <span
                          key={feature}
                          className="px-4 py-2 rounded-full text-sm font-medium bg-[#f1f5f9] text-[#475569]"
                        >
                          {feature}
                        </span>
                      ))}
                    </div>
                  </div>
                )}

                {product.specifications && Object.keys(product.specifications).length > 0 && (
                  <div className="rounded-[16px] border border-[#e2e8f0] overflow-hidden">
                    <table className="w-full text-sm">
                      <tbody>
                        {Object.entries(product.specifications).map(([key, value]) => (
                          <tr key={key} className="border-b border-[#e2e8f0] last:border-0">
                            <td className="px-5 py-3 font-semibold text-[#0a1628] bg-[#f8fafc]">
                              {key}
                            </td>
                            <td className="px-5 py-3 text-[#475569]">{value}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}

                <div className="mt-8 space-y-5">
                  {reviewsData && reviewsData.review_count > 0 && (
                    <div className="flex items-center gap-2">
                      <StarRating rating={Math.round(reviewsData.average_rating)} size="sm" />
                      <span className="text-sm text-[#64748b]">
                        {reviewsData.average_rating.toFixed(1)} ({reviewsData.review_count} reviews)
                      </span>
                    </div>
                  )}

                  {/* Quantity selector */}
                  <div className="flex items-center gap-4">
                    <span className="text-sm font-semibold text-[#0a1628]">Quantity</span>
                    <div className="flex items-center border border-[#e2e8f0] rounded-full bg-white">
                      <button
                        type="button"
                        onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                        disabled={quantity <= 1}
                        className="w-10 h-10 flex items-center justify-center text-[#475569] hover:text-[#0a1628] disabled:opacity-40"
                        aria-label="Decrease quantity"
                      >
                        <Minus className="w-4 h-4" />
                      </button>
                      <span className="w-10 text-center text-sm font-semibold text-[#0a1628]">{quantity}</span>
                      <button
                        type="button"
                        onClick={() => setQuantity((q) => q + 1)}
                        className="w-10 h-10 flex items-center justify-center text-[#475569] hover:text-[#0a1628]"
                        aria-label="Increase quantity"
                      >
                        <Plus className="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                  {/* Commerce actions */}
                  <div className="flex flex-col sm:flex-row gap-3">
                    <button
                      type="button"
                      disabled={adding || buying || product.stock_quantity <= 0}
                      onClick={async () => {
                        setAdding(true);
                        try {
                          await addItem(toCartProduct(product), quantity);
                          toastAddedToCart(product.name, quantity);
                        } catch {
                          // error already toasted in context
                        } finally {
                          setAdding(false);
                        }
                      }}
                      className={cn(
                        "flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-[#0d3b66] to-[#1a5276] shadow-lg shadow-[#0d3b66]/30 hover:-translate-y-0.5 transition-all",
                        (adding || buying || product.stock_quantity <= 0) && "opacity-70 cursor-not-allowed"
                      )}
                    >
                      {adding ? (
                        <Loader2 className="w-4 h-4 animate-spin" />
                      ) : (
                        <ShoppingCart className="w-4 h-4" />
                      )}
                      {adding ? "Adding..." : "Add to Cart"}
                    </button>
                    <button
                      type="button"
                      disabled={adding || buying || product.stock_quantity <= 0}
                      onClick={async () => {
                        setBuying(true);
                        try {
                          await addItem(toCartProduct(product), quantity);
                          router.push("/checkout");
                        } catch {
                          // error already toasted in context
                        } finally {
                          setBuying(false);
                        }
                      }}
                      className={cn(
                        "flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all",
                        (adding || buying || product.stock_quantity <= 0) && "opacity-70 cursor-not-allowed"
                      )}
                    >
                      {buying ? (
                        <Loader2 className="w-4 h-4 animate-spin" />
                      ) : (
                        <Zap className="w-4 h-4" />
                      )}
                      {buying ? "Please wait..." : "Buy Now"}
                    </button>
                  </div>

                  {/* Secondary distributor prompt */}
                  <p className="text-sm text-[#64748b]">
                    Interested in reseller or bulk pricing?{" "}
                    <Link href="/bulk-orders" className="font-semibold text-green-600 hover:text-green-700 underline underline-offset-2">
                      Request a wholesale quote
                    </Link>{" "}
                    or{" "}
                    <Link href="/distributor" className="font-semibold text-green-600 hover:text-green-700 underline underline-offset-2">
                      become a distributor
                    </Link>.
                  </p>
                </div>
              </div>
            </div>
          </Container>
        </section>

        {/* Reviews Section */}
        <section className="py-16 lg:py-24 bg-[#f8fafc]">
          <Container>
            <div className="grid lg:grid-cols-3 gap-12">
              <div className="lg:col-span-1">
                <h2 className="text-2xl font-extrabold text-[#0a1628] mb-4">Customer Reviews</h2>
                <p className="text-[#64748b] mb-6">
                  Share your experience with this product. Your feedback helps other customers make informed decisions.
                </p>
                <ReviewForm
                  productId={product.id}
                  onSubmit={async (data) => {
                    await submitReviewMutation.mutateAsync(data);
                  }}
                />
              </div>
              <div className="lg:col-span-2">
                {reviewsData && (
                  <ReviewList
                    reviews={reviewsData.reviews}
                    averageRating={reviewsData.average_rating}
                    reviewCount={reviewsData.review_count}
                  />
                )}
              </div>
            </div>
          </Container>
        </section>

        {/* Recently Viewed */}
        {recentlyViewed.length > 0 && (
          <section className="py-16 lg:py-24 bg-white">
            <Container>
              <SectionHeader title="Recently Viewed" subtitle="Products you have browsed recently." />
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                {recentlyViewed.map((recent) => (
                  <Link
                    key={recent.id}
                    href={`/products/${recent.slug}`}
                    className="group bg-white rounded-[20px] overflow-hidden border border-[#e2e8f0] shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-[#7db8ec] transition-all"
                  >
                    <div className="relative p-6 min-h-[180px] flex items-center justify-center bg-gradient-to-b from-[#f8fafc] to-white">
                      <Image
                        src={recent.image}
                        alt={recent.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                        className="object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                    <div className="p-5">
                      <span className="text-xs font-semibold text-green-600 uppercase tracking-wider">{recent.category}</span>
                      <h3 className="text-base font-bold text-[#0a1628] mt-1 mb-1">{recent.name}</h3>
                      <p className="text-[#0d3b66] font-extrabold">{formatPrice(Number(recent.price))}</p>
                    </div>
                  </Link>
                ))}
              </div>
            </Container>
          </section>
        )}

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <section className="py-16 lg:py-24 bg-[#f8fafc]">
            <Container>
              <SectionHeader title="Related Products" subtitle="More solutions from the same category." />
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                {relatedProducts.map((related) => (
                  <Link
                    key={related.id}
                    href={`/products/${related.slug}`}
                    className="group bg-white rounded-[20px] overflow-hidden border border-[#e2e8f0] shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-[#7db8ec] transition-all"
                  >
                    <div className="relative p-6 min-h-[200px] flex items-center justify-center bg-gradient-to-b from-[#f8fafc] to-white">
                      <Image
                        src={related.images[0]?.image || "/assets/images/products/placeholder.png"}
                        alt={related.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                        className="object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                    <div className="p-6">
                      <h3 className="text-lg font-bold text-[#0a1628] mb-1">{related.name}</h3>
                      <p className="text-[#0d3b66] font-extrabold">{formatPrice(Number(related.price))}</p>
                    </div>
                  </Link>
                ))}
              </div>
            </Container>
          </section>
        )}

        <CTASection
          title="Need wholesale or corporate pricing?"
          description="Request a tailored quotation for bulk orders, institutions, or resale partnerships."
          buttonText="Request a Quote"
          buttonHref="/bulk-orders"
          secondaryButton={{ text: "Become a Distributor", href: "/distributor" }}
          light
        />
      </main>
    </>
  );
}
