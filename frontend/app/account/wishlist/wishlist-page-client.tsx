"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { Heart, Trash2, Loader2, FileText, ArrowRight } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useWishlist } from "@/lib/wishlist-context";
import { formatPrice } from "@/lib/utils";
import { toastSuccess } from "@/lib/toast-utils";
import type { Product } from "@/types";

interface ProductCardProps {
  product: Product;
  onRemove: () => void;
  isLoading?: boolean;
}

function ProductCard({ product, onRemove, isLoading }: ProductCardProps) {
  const image = product.images?.[0]?.image || "/assets/images/products/placeholder.png";

  return (
    <div className="bg-surface-card rounded-[20px] border border-default shadow-sm overflow-hidden">
      <div className="relative aspect-square bg-surface-page p-6">
        <Image src={image} alt={product.name} fill className="object-contain p-4" sizes="300px" />
      </div>
      <div className="p-5">
        <Link href={`/products/${product.slug}`} className="font-bold text-primary-900 hover:text-secondary-600 line-clamp-2">
          {product.name}
        </Link>
        <p className="text-sm text-muted mt-1">SKU: {product.sku}</p>
        <p className="text-lg font-extrabold text-primary-500 mt-2">{formatPrice(Number(product.price || 0))}</p>
        <div className="flex flex-col gap-2 mt-4">
          <Link
            href={`/request-quote?product=${encodeURIComponent(product.slug)}`}
            className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-secondary-600 text-white text-sm font-semibold rounded-xl hover:bg-secondary-600/90"
          >
            <FileText className="w-4 h-4" />
            Request a Quote
          </Link>
          <button
            type="button"
            onClick={onRemove}
            disabled={isLoading}
            className="inline-flex items-center justify-center gap-2 px-4 py-2 border border-default text-sm font-semibold text-primary-900 rounded-xl hover:bg-surface-page disabled:opacity-50"
          >
            {isLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Trash2 className="w-4 h-4" />}
            Remove
          </button>
        </div>
      </div>
    </div>
  );
}

export function WishlistPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const {
    wishlist,
    savedItems,
    isLoading,
    removeFromWishlist,
    removeFromSavedItems,
  } = useWishlist();
  const [activeTab, setActiveTab] = useState<"wishlist" | "saved">("wishlist");
  const [actionId, setActionId] = useState<number | null>(null);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleRemoveWishlist = async (productId: number) => {
    setActionId(productId);
    try {
      await removeFromWishlist(productId);
      toastSuccess("Removed from wishlist");
    } finally {
      setActionId(null);
    }
  };

  const handleRemoveSaved = async (productId: number) => {
    setActionId(productId);
    try {
      await removeFromSavedItems(productId);
      toastSuccess("Removed from saved items");
    } finally {
      setActionId(null);
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

  const items = activeTab === "wishlist" ? wishlist : savedItems;

  return (
    <>
      <PageHero
        title="Wishlist & Saved Items"
        subtitle="Products you love and items you have saved"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Wishlist" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            {/* Tabs */}
            <div className="flex items-center gap-2 border-b border-default mb-6">
              <button
                type="button"
                onClick={() => setActiveTab("wishlist")}
                className={`px-4 py-3 text-sm font-semibold border-b-2 transition-colors ${
                  activeTab === "wishlist"
                    ? "border-secondary-600 text-secondary-600"
                    : "border-transparent text-muted hover:text-primary-900"
                }`}
              >
                Wishlist ({wishlist.length})
              </button>
              <button
                type="button"
                onClick={() => setActiveTab("saved")}
                className={`px-4 py-3 text-sm font-semibold border-b-2 transition-colors ${
                  activeTab === "saved"
                    ? "border-secondary-600 text-secondary-600"
                    : "border-transparent text-muted hover:text-primary-900"
                }`}
              >
                Saved Items ({savedItems.length})
              </button>
            </div>

            {isLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-secondary-500 mx-auto" />
              </div>
            ) : items.length === 0 ? (
              <div className="py-16 text-center">
                <Heart className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                <h3 className="text-lg font-bold text-primary-900 mb-2">
                  {activeTab === "wishlist" ? "Your wishlist is empty" : "No saved items"}
                </h3>
                <p className="text-muted mb-6">
                  {activeTab === "wishlist"
                    ? "Save products you love to find them quickly."
                    : "Save products here to review or request a quote later."}
                </p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base"
                >
                  <ArrowRight className="w-4 h-4" />
                  View Products
                </Link>
              </div>
            ) : (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {items.map((item) => (
                  <ProductCard
                    key={item.id}
                    product={item.product}
                    isLoading={actionId === item.product.id}
                    onRemove={() =>
                      activeTab === "wishlist" ? handleRemoveWishlist(item.product.id) : handleRemoveSaved(item.product.id)
                    }
                  />
                ))}
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
