"use client";

import Link from "next/link";
import Image from "next/image";
import { ShoppingBag } from "lucide-react";
import { Container } from "@/components/common/container";
import { SkeletonGrid } from "@/components/ui/skeleton-grid";
import { ApiError } from "@/components/ui/api-error";
import { useProducts } from "@/hooks/use-products";
import { formatPrice } from "@/lib/utils";

export function EmptyCart() {
  const { data: products, isLoading, error, refetch } = useProducts();
  const recommended = products?.slice(0, 3) || [];

  return (
    <div className="text-center py-12 lg:py-16">
      <div className="w-20 h-20 rounded-full bg-[#f1f5f9] flex items-center justify-center mx-auto mb-6">
        <ShoppingBag className="w-10 h-10 text-[#94a3b8]" aria-hidden="true" />
      </div>
      <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628] mb-2">Your cart is empty</h2>
      <p className="text-[#64748b] mb-6 max-w-md mx-auto">
        Looks like you haven&apos;t added anything yet. Explore our products and find the perfect
        fabric care solution.
      </p>
      <Link
        href="/products"
        className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all"
      >
        Start Shopping
      </Link>

      {recommended.length > 0 && (
        <div className="mt-14 text-left">
          <Container>
            <h3 className="text-lg font-bold text-[#0a1628] mb-6 text-center">Recommended for you</h3>
            {isLoading && <SkeletonGrid count={3} />}
            {error && (
              <ApiError message="Failed to load recommendations." onRetry={refetch} />
            )}
            {!isLoading && !error && (
              <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                {recommended.map((product) => (
                  <Link
                    key={product.id}
                    href={`/products/${product.slug}`}
                    className="group bg-white rounded-[20px] overflow-hidden border border-[#e2e8f0] shadow-sm hover:-translate-y-2 hover:shadow-xl hover:border-[#7db8ec] transition-all"
                  >
                    <div className="relative p-6 min-h-[180px] flex items-center justify-center bg-gradient-to-b from-[#f8fafc] to-white">
                      <Image
                        src={product.images[0]?.image || "/assets/images/products/placeholder.png"}
                        alt={product.name}
                        fill
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                        className="object-contain p-4 group-hover:scale-105 transition-transform duration-500"
                      />
                    </div>
                    <div className="p-5">
                      <span className="text-xs font-semibold text-green-600 uppercase tracking-wider">
                        {product.category.name}
                      </span>
                      <h4 className="text-base font-bold text-[#0a1628] mt-1 mb-1">{product.name}</h4>
                      <p className="text-[#0d3b66] font-extrabold">{formatPrice(Number(product.price))}</p>
                    </div>
                  </Link>
                ))}
              </div>
            )}
          </Container>
        </div>
      )}
    </div>
  );
}
