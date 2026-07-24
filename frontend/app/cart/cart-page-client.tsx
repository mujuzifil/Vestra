"use client";

import { useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { useRouter } from "next/navigation";
import {
  Minus,
  Plus,
  Trash2,
  ShoppingBag,
  ArrowRight,
  Truck,
  Receipt,
  AlertCircle,
  Heart,
  Loader2,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { EmptyCart } from "@/components/cart/empty-cart";
import { useCartContext } from "@/lib/cart-context";
import { formatPrice, cn } from "@/lib/utils";
import {
  toastUpdatedQuantity,
  toastRemovedFromCart,
  toastCartCleared,
  toastError,
} from "@/lib/toast-utils";

export function CartPageClient() {
  const router = useRouter();
  const { cart, updateItem, removeItem, clear } = useCartContext();
  const [removingId, setRemovingId] = useState<number | null>(null);
  const [clearing, setClearing] = useState(false);

  if (!cart || cart.items.length === 0) {
    return (
      <main>
        <PageHero title="Shopping Cart" breadcrumb={[{ label: "Cart" }]} />
        <section className="py-12 lg:py-20 bg-white">
          <Container>
            <EmptyCart />
          </Container>
        </section>
      </main>
    );
  }

  const handleQuantityChange = async (itemId: number, newQuantity: number, productName: string) => {
    if (newQuantity < 1) return;
    try {
      await updateItem(itemId, newQuantity);
      toastUpdatedQuantity(productName, newQuantity);
    } catch {
      // error already toasted in context
    }
  };

  const handleRemove = async (itemId: number, productName: string) => {
    if (!confirm(`Remove ${productName} from your cart?`)) return;
    setRemovingId(itemId);
    try {
      await removeItem(itemId);
      toastRemovedFromCart(productName);
    } catch {
      toastError("Could not remove item.");
    } finally {
      setRemovingId(null);
    }
  };

  const handleClear = async () => {
    if (!confirm("Are you sure you want to clear your cart?")) return;
    setClearing(true);
    try {
      await clear();
      toastCartCleared();
    } catch {
      toastError("Could not clear cart.");
    } finally {
      setClearing(false);
    }
  };

  const estimatedTotal = Number(cart.subtotal || 0);

  return (
    <main>
      <PageHero title="Shopping Cart" breadcrumb={[{ label: "Cart" }]} />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <div className="grid lg:grid-cols-3 gap-8 lg:gap-10 items-start">
            {/* Cart items */}
            <div className="lg:col-span-2 space-y-4">
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm overflow-hidden">
                {/* Desktop header */}
                <div className="hidden md:grid grid-cols-12 gap-4 p-5 border-b border-[#e2e8f0] text-sm font-semibold text-[#64748b]">
                  <div className="col-span-6">Product</div>
                  <div className="col-span-2 text-center">Price</div>
                  <div className="col-span-2 text-center">Quantity</div>
                  <div className="col-span-2 text-right">Total</div>
                </div>

                <div className="divide-y divide-[#f1f5f9]">
                  {cart.items.map((item) => {
                    const product = item.product;
                    const image = product.images?.[0]?.image || "/assets/images/products/placeholder.png";
                    const isLowStock = product.stock_quantity > 0 && product.stock_quantity <= 5;
                    const outOfStock = product.stock_quantity <= 0;

                    return (
                      <div
                        key={item.id}
                        className="grid grid-cols-1 md:grid-cols-12 gap-4 p-5 items-center"
                      >
                        {/* Product info */}
                        <div className="md:col-span-6 flex gap-4">
                          <div className="relative w-20 h-20 rounded-xl bg-[#f8fafc] border border-[#e2e8f0] overflow-hidden flex-shrink-0">
                            <Image
                              src={image}
                              alt={product.name}
                              fill
                              className="object-contain p-2"
                            />
                          </div>
                          <div className="flex-1 min-w-0">
                            <Link
                              href={`/products/${product.slug}`}
                              className="font-bold text-[#0a1628] hover:text-green-600 transition-colors line-clamp-2"
                            >
                              {product.name}
                            </Link>
                            <p className="text-xs text-[#64748b] mt-1">SKU: {product.sku}</p>
                            {outOfStock ? (
                              <span className="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-red-600">
                                <AlertCircle className="w-3 h-3" /> Out of stock
                              </span>
                            ) : isLowStock ? (
                              <span className="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-amber-600">
                                <AlertCircle className="w-3 h-3" /> Only {product.stock_quantity} left
                              </span>
                            ) : null}
                            <div className="flex items-center gap-3 mt-3 md:hidden">
                              <button
                                onClick={() => handleRemove(item.id, product.name)}
                                disabled={removingId === item.id}
                                className="inline-flex items-center gap-1 text-xs font-medium text-[#94a3b8] hover:text-red-500 transition-colors"
                              >
                                {removingId === item.id ? (
                                  <Loader2 className="w-3 h-3 animate-spin" />
                                ) : (
                                  <Trash2 className="w-3 h-3" />
                                )}
                                Remove
                              </button>
                              <button
                                disabled
                                className="inline-flex items-center gap-1 text-xs font-medium text-[#94a3b8] cursor-not-allowed"
                                title="Save for later coming soon"
                              >
                                <Heart className="w-3 h-3" /> Save for later
                              </button>
                            </div>
                          </div>
                        </div>

                        {/* Price */}
                        <div className="md:col-span-2 md:text-center">
                          <span className="md:hidden text-sm text-[#64748b]">Price: </span>
                          <span className="font-semibold text-[#0a1628]">
                            {formatPrice(Number(product.price || 0))}
                          </span>
                        </div>

                        {/* Quantity */}
                        <div className="md:col-span-2 md:text-center">
                          <div className="inline-flex items-center border border-[#e2e8f0] rounded-full bg-white">
                            <button
                              type="button"
                              onClick={() => handleQuantityChange(item.id, item.quantity - 1, product.name)}
                              disabled={item.quantity <= 1}
                              className="w-10 h-10 flex items-center justify-center text-[#475569] hover:text-[#0a1628] disabled:opacity-40"
                              aria-label="Decrease quantity"
                            >
                              <Minus className="w-4 h-4" />
                            </button>
                            <input
                              type="number"
                              min={1}
                              max={product.stock_quantity || 999}
                              value={item.quantity}
                              onChange={(e) => {
                                const value = parseInt(e.target.value, 10);
                                if (!isNaN(value)) {
                                  handleQuantityChange(item.id, value, product.name);
                                }
                              }}
                              className="w-12 text-center text-sm font-semibold text-[#0a1628] bg-transparent border-none focus:ring-0 p-0"
                              aria-label="Quantity"
                            />
                            <button
                              type="button"
                              onClick={() => handleQuantityChange(item.id, item.quantity + 1, product.name)}
                              disabled={item.quantity >= (product.stock_quantity || 999)}
                              className="w-10 h-10 flex items-center justify-center text-[#475569] hover:text-[#0a1628] disabled:opacity-40"
                              aria-label="Increase quantity"
                            >
                              <Plus className="w-4 h-4" />
                            </button>
                          </div>
                        </div>

                        {/* Total */}
                        <div className="md:col-span-2 md:text-right">
                          <span className="md:hidden text-sm text-[#64748b]">Total: </span>
                          <span className="font-bold text-[#0d3b66]">
                            {formatPrice(Number(item.line_total || 0))}
                          </span>
                        </div>

                        {/* Desktop actions */}
                        <div className="hidden md:col-span-12 md:flex items-center justify-end gap-4 pt-2">
                          <button
                            onClick={() => handleRemove(item.id, product.name)}
                            disabled={removingId === item.id}
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-[#64748b] hover:text-red-500 transition-colors"
                          >
                            {removingId === item.id ? (
                              <Loader2 className="w-4 h-4 animate-spin" />
                            ) : (
                              <Trash2 className="w-4 h-4" />
                            )}
                            Remove
                          </button>
                          <button
                            disabled
                            className="inline-flex items-center gap-1.5 text-sm font-medium text-[#94a3b8] cursor-not-allowed"
                            title="Save for later coming soon"
                          >
                            <Heart className="w-4 h-4" /> Save for later
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-3">
                <Link
                  href="/products"
                  className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                >
                  <ShoppingBag className="w-4 h-4" />
                  Continue Shopping
                </Link>
                <button
                  onClick={handleClear}
                  disabled={clearing}
                  className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-[#64748b] bg-white border border-[#e2e8f0] hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors disabled:opacity-60"
                >
                  {clearing ? <Loader2 className="w-4 h-4 animate-spin" /> : <Trash2 className="w-4 h-4" />}
                  Clear Cart
                </button>
              </div>
            </div>

            {/* Order summary */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 sticky top-24">
                <h2 className="text-lg font-bold text-[#0a1628] mb-6">Order Summary</h2>

                <div className="space-y-4 mb-6">
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b]">Subtotal</span>
                    <span className="font-semibold text-[#0a1628]">{formatPrice(Number(cart.subtotal || 0))}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b] flex items-center gap-1.5">
                      <Truck className="w-4 h-4" /> Shipping
                    </span>
                    <span className="font-medium text-[#94a3b8]">Calculated at checkout</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b] flex items-center gap-1.5">
                      <Receipt className="w-4 h-4" /> Tax
                    </span>
                    <span className="font-medium text-[#94a3b8]">Calculated at checkout</span>
                  </div>
                </div>

                <div className="border-t border-[#e2e8f0] pt-4 mb-6">
                  <div className="flex justify-between text-lg font-bold text-[#0a1628]">
                    <span>Estimated Total</span>
                    <span>{formatPrice(estimatedTotal)}</span>
                  </div>
                </div>

                <button
                  onClick={() => router.push("/checkout")}
                  className={cn(
                    "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all"
                  )}
                >
                  Proceed to Checkout
                  <ArrowRight className="w-4 h-4" />
                </button>

                <p className="text-xs text-[#94a3b8] text-center mt-4">
                  Shipping, taxes, and discounts will be calculated at checkout.
                </p>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </main>
  );
}
