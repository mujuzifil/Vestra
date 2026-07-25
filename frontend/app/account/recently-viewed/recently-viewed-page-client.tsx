"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { Clock, Loader2, ShoppingBag, Trash2, X } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useRecentlyViewed, useRemoveRecentlyViewed, useClearRecentlyViewed } from "@/hooks/use-recently-viewed";
import { formatPrice } from "@/lib/utils";
import { toastSuccess } from "@/lib/toast-utils";

export function RecentlyViewedPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading } = useRecentlyViewed();
  const removeMutation = useRemoveRecentlyViewed();
  const clearMutation = useClearRecentlyViewed();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleRemove = (productId: number) => {
    removeMutation.mutate(productId);
  };

  const handleClear = () => {
    if (confirm("Clear your entire recently viewed history?")) {
      clearMutation.mutate(undefined, {
        onSuccess: () => toastSuccess("Recently viewed history cleared"),
      });
    }
  };

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
        title="Recently Viewed"
        subtitle="Products you have browsed recently"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Recently Viewed" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-bold text-primary-900">Browsing History</h2>
              {items.length > 0 && (
                <button
                  type="button"
                  onClick={handleClear}
                  disabled={clearMutation.isPending}
                  className="inline-flex items-center gap-1.5 text-sm font-semibold text-danger-600 hover:text-danger-600 disabled:opacity-50"
                >
                  {clearMutation.isPending ? <Loader2 className="w-4 h-4 animate-spin" /> : <X className="w-4 h-4" />}
                  Clear History
                </button>
              )}
            </div>

            {isLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-secondary-500 mx-auto" />
              </div>
            ) : items.length === 0 ? (
              <div className="py-16 text-center">
                <Clock className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-primary-900 mb-2">No recently viewed products</h3>
                <p className="text-muted mb-6">Products you view will appear here.</p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base"
                >
                  <ShoppingBag className="w-4 h-4" />
                  Start Shopping
                </Link>
              </div>
            ) : (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {items.map((item) => {
                  const product = item.product;
                  const image = product.images?.[0]?.image || "/assets/images/products/placeholder.png";
                  return (
                    <div
                      key={item.id}
                      className="bg-surface-card rounded-[20px] border border-default shadow-sm overflow-hidden group"
                    >
                      <div className="relative aspect-square bg-surface-page p-6">
                        <Image src={image} alt={product.name} fill className="object-contain p-4" sizes="300px" />
                      </div>
                      <div className="p-5">
                        <Link
                          href={`/products/${product.slug}`}
                          className="font-bold text-primary-900 hover:text-secondary-600 line-clamp-2"
                        >
                          {product.name}
                        </Link>
                        <p className="text-sm text-muted mt-1">
                          Viewed {new Date(item.viewed_at).toLocaleDateString()}
                        </p>
                        <p className="text-lg font-extrabold text-primary-500 mt-2">
                          {formatPrice(Number(product.price || 0))}
                        </p>
                        <button
                          type="button"
                          onClick={() => handleRemove(product.id)}
                          disabled={removeMutation.isPending}
                          className="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-danger-600 hover:text-danger-600 disabled:opacity-50"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          Remove
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
