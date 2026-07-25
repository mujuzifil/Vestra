"use client";

import Link from "next/link";
import Image from "next/image";
import { Scale, X, ShoppingBag, Trash2, Check } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useCompare } from "@/lib/compare-context";
import { useCartContext, toCartProduct } from "@/lib/cart-context";
import { formatPrice } from "@/lib/utils";
import { toastAddedToCart } from "@/lib/toast-utils";

export function ComparePageClient() {
  const { items, removeFromCompare, clearCompare } = useCompare();
  const { addItem } = useCartContext();

  const specificationKeys = Array.from(
    new Set(items.flatMap((product) => (product.specifications ? Object.keys(product.specifications) : [])))
  );

  return (
    <main>
      <PageHero
        title="Compare Products"
        subtitle="Compare features, pricing, and specifications side by side"
        breadcrumb={[{ label: "Compare" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          {items.length === 0 ? (
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-12 text-center">
              <Scale className="w-14 h-14 mx-auto mb-4 text-placeholder" />
              <h2 className="text-lg font-bold text-primary-900 mb-2">No products to compare</h2>
              <p className="text-muted mb-6">Add products to your comparison list to see them here.</p>
              <Link
                href="/products"
                className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base"
              >
                <ShoppingBag className="w-4 h-4" />
                Browse Products
              </Link>
            </div>
          ) : (
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm overflow-hidden">
              <div className="p-4 lg:p-6 border-b border-default flex items-center justify-between">
                <h2 className="text-lg font-bold text-primary-900">Comparing {items.length} products</h2>
                <button
                  type="button"
                  onClick={clearCompare}
                  className="inline-flex items-center gap-1.5 text-sm font-semibold text-danger-600 hover:text-danger-600"
                >
                  <Trash2 className="w-4 h-4" />
                  Clear All
                </button>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full min-w-[600px]">
                  <thead>
                    <tr>
                      <th className="p-4 text-left text-sm font-semibold text-primary-900 bg-surface-page border-b border-default w-40">Feature</th>
                      {items.map((product) => (
                        <th key={product.id} className="p-4 text-left border-b border-default min-w-[220px]">
                          <div className="relative aspect-square bg-surface-page rounded-xl mb-3 overflow-hidden">
                            <Image
                              src={product.images?.[0]?.image || "/assets/images/products/placeholder.png"}
                              alt={product.name}
                              fill
                              className="object-contain p-4"
                              sizes="220px"
                            />
                          </div>
                          <Link
                            href={`/products/${product.slug}`}
                            className="font-bold text-primary-900 hover:text-secondary-600 line-clamp-2"
                          >
                            {product.name}
                          </Link>
                          <button
                            type="button"
                            onClick={() => removeFromCompare(product.id)}
                            className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-danger-600 hover:text-danger-600"
                          >
                            <X className="w-3 h-3" /> Remove
                          </button>
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    <tr className="border-b border-default">
                      <td className="p-4 text-sm font-semibold text-primary-900 bg-surface-page">Price</td>
                      {items.map((product) => (
                        <td key={product.id} className="p-4">
                          <span className="text-lg font-extrabold text-primary-500">
                            {formatPrice(Number(product.price || 0))}
                          </span>
                        </td>
                      ))}
                    </tr>
                    <tr className="border-b border-default">
                      <td className="p-4 text-sm font-semibold text-primary-900 bg-surface-page">Availability</td>
                      {items.map((product) => (
                        <td key={product.id} className="p-4">
                          {product.stock_quantity > 0 ? (
                            <span className="inline-flex items-center gap-1 text-sm text-success-600 font-medium">
                              <Check className="w-4 h-4" /> In Stock ({product.stock_quantity})
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 text-sm text-danger-600 font-medium">
                              <X className="w-4 h-4" /> Out of Stock
                            </span>
                          )}
                        </td>
                      ))}
                    </tr>
                    <tr className="border-b border-default">
                      <td className="p-4 text-sm font-semibold text-primary-900 bg-surface-page">SKU</td>
                      {items.map((product) => (
                        <td key={product.id} className="p-4 text-sm text-muted">{product.sku}</td>
                      ))}
                    </tr>
                    {specificationKeys.map((key) => (
                      <tr key={key} className="border-b border-default">
                        <td className="p-4 text-sm font-semibold text-primary-900 bg-surface-page capitalize">{key}</td>
                        {items.map((product) => (
                          <td key={product.id} className="p-4 text-sm text-muted">
                            {product.specifications?.[key] ?? "—"}
                          </td>
                        ))}
                      </tr>
                    ))}
                    <tr>
                      <td className="p-4 bg-surface-page"></td>
                      {items.map((product) => (
                        <td key={product.id} className="p-4">
                          <button
                            type="button"
                            disabled={product.stock_quantity <= 0}
                            onClick={async () => {
                              await addItem(toCartProduct(product), 1);
                              toastAddedToCart(product.name, 1);
                            }}
                            className="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-secondary-600 text-white text-sm font-semibold rounded-xl hover:bg-secondary-600/90 disabled:opacity-50"
                          >
                            <ShoppingBag className="w-4 h-4" />
                            Add to Cart
                          </button>
                        </td>
                      ))}
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </Container>
      </section>
    </main>
  );
}
