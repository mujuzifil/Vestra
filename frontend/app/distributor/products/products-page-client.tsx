"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import Image from "next/image";
import { Package, Search, Loader2 } from "lucide-react";
import { useDistributorProducts } from "@/hooks/use-distributor-products";
import { EmptyState } from "@/components/common/empty-state";
import type { DistributorProduct } from "@/types";

function ProductCard({ product }: { product: DistributorProduct }) {
  const image = product.images?.[0];
  const price = product.distributor_price || product.negotiated_price || product.tier_price || product.price;

  return (
    <Link
      href={`/distributor/products/${product.slug}`}
      className="group bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm overflow-hidden hover:shadow-md transition-shadow"
    >
      <div className="relative aspect-square bg-[#f8fafc]">
        {image ? (
          <Image src={image.image} alt={image.alt_text || product.name} fill className="object-cover" />
        ) : (
          <div className="absolute inset-0 flex items-center justify-center text-[#94a3b8]">
            <Package className="w-12 h-12" />
          </div>
        )}
      </div>
      <div className="p-5">
        <p className="text-xs font-semibold text-green-600 uppercase tracking-wider mb-1">{product.category?.name}</p>
        <h3 className="font-bold text-[#0a1628] group-hover:text-green-600 transition-colors line-clamp-1">{product.name}</h3>
        <p className="text-sm text-[#64748b] mt-1 line-clamp-2">{product.short_description}</p>
        <div className="flex items-center justify-between mt-4">
          <span className="font-extrabold text-[#0a1628]">UGX {price}</span>
          <span className="text-xs text-[#94a3b8]">SKU: {product.sku}</span>
        </div>
      </div>
    </Link>
  );
}

export function ProductsPageClient() {
  const { data: products, isLoading } = useDistributorProducts();
  const [search, setSearch] = useState("");

  const filtered = useMemo(() => {
    const term = search.toLowerCase().trim();
    if (!term) return products || [];
    return (products || []).filter(
      (p) =>
        p.name.toLowerCase().includes(term) ||
        p.sku.toLowerCase().includes(term) ||
        p.category?.name?.toLowerCase().includes(term)
    );
  }, [products, search]);

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-[#0a1628]">Products</h1>
          <p className="text-[#64748b]">Browse products with distributor pricing.</p>
        </div>
        <div className="relative max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
          <input
            type="text"
            placeholder="Search products..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>
      </div>

      {isLoading ? (
        <div className="min-h-[50vh] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : filtered.length === 0 ? (
        <EmptyState title="No products found" description="Try adjusting your search or check back later." />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {filtered.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
}
