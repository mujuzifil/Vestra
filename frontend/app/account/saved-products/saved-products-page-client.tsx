"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { Bookmark, Loader2, FileText, ArrowRight, Tag } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useSavedItems } from "@/hooks/use-saved-items";

function ProductCard({ item }: { item: { id: number; product: { id: number; name: string; slug: string; category: { name: string }; benefits: string[] | null; images: { image: string; alt_text: string | null }[] } } }) {
  const { product } = item;
  const image = product.images?.[0]?.image || "/assets/images/products/placeholder.png";
  const applications = product.benefits?.length ? product.benefits : null;

  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm overflow-hidden flex flex-col">
      <div className="relative aspect-square bg-surface-page p-6">
        <Image
          src={image}
          alt={product.images?.[0]?.alt_text || product.name}
          fill
          className="object-contain p-4"
          sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
        />
      </div>
      <div className="p-5 flex-1 flex flex-col">
        <Link
          href={`/products/${product.slug}`}
          className="font-bold text-text-heading hover:text-secondary-600 line-clamp-2"
        >
          {product.name}
        </Link>
        <div className="flex items-center gap-1.5 text-sm text-muted mt-1">
          <Tag className="w-3.5 h-3.5" />
          {product.category?.name || "General"}
        </div>

        {applications && (
          <div className="mt-3">
            <p className="text-xs font-semibold text-text-heading mb-1">Applications</p>
            <p className="text-sm text-muted line-clamp-2">{applications.join(", ")}</p>
          </div>
        )}

        <p className="text-xs text-placeholder mt-3">Multiple sizes available</p>

        <div className="mt-auto pt-4 flex flex-col gap-2">
          <Link
            href={`/request-quote?product=${encodeURIComponent(product.slug)}`}
            className="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-secondary-600 text-white text-sm font-semibold rounded-xl hover:opacity-90"
          >
            <FileText className="w-4 h-4" />
            Request Quote
          </Link>
          <Link
            href={`/products/${product.slug}`}
            className="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-default text-body text-sm font-semibold rounded-xl hover:bg-surface-page"
          >
            View Product
          </Link>
        </div>
      </div>
    </div>
  );
}

export function SavedProductsPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading } = useSavedItems();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  const items = data?.items ?? [];

  return (
    <>
      <PageHero
        title="Saved Products"
        subtitle="Products you have saved for future reference"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Saved Products" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            {isLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-secondary-500 mx-auto" />
              </div>
            ) : items.length === 0 ? (
              <div className="py-16 text-center">
                <Bookmark className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-text-heading mb-2">No saved products yet</h3>
                <p className="text-muted mb-6 max-w-md mx-auto">
                  Save products to build your quote list.
                </p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
                >
                  <ArrowRight className="w-4 h-4" />
                  Browse Products
                </Link>
              </div>
            ) : (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {items.map((item) => (
                  <ProductCard key={item.id} item={item} />
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
