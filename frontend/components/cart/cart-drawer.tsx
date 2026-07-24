"use client";

import { useEffect, useRef } from "react";
import Link from "next/link";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { X, Minus, Plus, ShoppingBag, Trash2, ArrowRight, AlertCircle } from "lucide-react";
import { useCartContext } from "@/lib/cart-context";
import { EmptyCart } from "@/components/cart/empty-cart";
import { formatPrice, cn } from "@/lib/utils";
import { toastUpdatedQuantity, toastRemovedFromCart, toastError } from "@/lib/toast-utils";

export function CartDrawer({ open, onClose }: { open: boolean; onClose: () => void }) {
  const router = useRouter();
  const { cart, itemCount, updateItem, removeItem } = useCartContext();
  const drawerRef = useRef<HTMLDivElement>(null);

  // Close on Escape
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape" && open) {
        onClose();
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [open, onClose]);

  // Lock body scroll when open
  useEffect(() => {
    if (open) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

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
    try {
      await removeItem(itemId);
      toastRemovedFromCart(productName);
    } catch {
      toastError("Could not remove item.");
    }
  };

  return (
    <>
      {/* Overlay */}
      <div
        className={cn(
          "fixed inset-0 bg-black/50 z-[60] transition-opacity",
          open ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
        aria-hidden={!open}
      />
      {/* Drawer */}
      <div
        ref={drawerRef}
        className={cn(
          "fixed top-0 right-0 h-full w-full sm:max-w-md bg-white z-[70] shadow-2xl transition-transform duration-300 flex flex-col",
          open ? "translate-x-0" : "translate-x-full"
        )}
        role="dialog"
        aria-modal="true"
        aria-label="Shopping cart"
      >
        <div className="flex items-center justify-between p-5 border-b border-[#e2e8f0]">
          <h2 className="text-lg font-bold text-[#0a1628] flex items-center gap-2">
            <ShoppingBag className="w-5 h-5" />
            Your Cart ({itemCount})
          </h2>
          <button
            onClick={onClose}
            className="p-2 rounded-full hover:bg-[#f8fafc] transition-colors"
            aria-label="Close cart"
          >
            <X className="w-5 h-5 text-[#64748b]" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-5">
          {!cart || cart.items.length === 0 ? (
            <EmptyCart />
          ) : (
            <div className="space-y-4">
              {cart.items.map((item) => {
                const product = item.product;
                const image = product.images?.[0]?.image || "/assets/images/products/placeholder.png";
                const outOfStock = product.stock_quantity <= 0;
                const isLowStock = product.stock_quantity > 0 && product.stock_quantity <= 5;

                return (
                  <div key={item.id} className="flex gap-4 p-3 rounded-xl bg-[#f8fafc]">
                    <div className="relative w-20 h-20 rounded-lg bg-white border border-[#e2e8f0] overflow-hidden flex-shrink-0">
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
                        onClick={onClose}
                        className="font-semibold text-[#0a1628] text-sm truncate hover:text-green-600 transition-colors"
                      >
                        {product.name}
                      </Link>
                      <p className="text-xs text-[#64748b] mt-0.5">SKU: {product.sku}</p>
                      <p className="text-xs font-semibold text-[#0d3b66] mt-0.5">
                        {formatPrice(Number(product.price || 0))}
                      </p>

                      {outOfStock ? (
                        <span className="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-red-600">
                          <AlertCircle className="w-3 h-3" /> Out of stock
                        </span>
                      ) : isLowStock ? (
                        <span className="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-amber-600">
                          <AlertCircle className="w-3 h-3" /> Only {product.stock_quantity} left
                        </span>
                      ) : null}

                      <div className="flex items-center gap-2 mt-2">
                        <button
                          onClick={() => handleQuantityChange(item.id, item.quantity - 1, product.name)}
                          disabled={item.quantity <= 1}
                          className="w-8 h-8 rounded-full bg-white border border-[#e2e8f0] flex items-center justify-center hover:border-green-500 transition-colors disabled:opacity-40"
                          aria-label="Decrease quantity"
                        >
                          <Minus className="w-3 h-3" />
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
                          className="w-10 text-center text-sm font-semibold text-[#0a1628] bg-transparent border-none focus:ring-0 p-0"
                          aria-label="Quantity"
                        />
                        <button
                          onClick={() => handleQuantityChange(item.id, item.quantity + 1, product.name)}
                          disabled={item.quantity >= (product.stock_quantity || 999)}
                          className="w-8 h-8 rounded-full bg-white border border-[#e2e8f0] flex items-center justify-center hover:border-green-500 transition-colors disabled:opacity-40"
                          aria-label="Increase quantity"
                        >
                          <Plus className="w-3 h-3" />
                        </button>
                        <button
                          onClick={() => handleRemove(item.id, product.name)}
                          className="ml-auto p-1.5 text-[#94a3b8] hover:text-red-500 transition-colors"
                          aria-label={`Remove ${product.name}`}
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {cart && cart.items.length > 0 && (
          <div className="p-5 border-t border-[#e2e8f0] space-y-4">
            <div className="flex items-center justify-between text-sm">
              <span className="text-[#64748b]">Subtotal</span>
              <span className="font-bold text-[#0a1628]">{formatPrice(Number(cart.subtotal || 0))}</span>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <Link
                href="/cart"
                onClick={onClose}
                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full font-semibold text-sm text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
              >
                View Cart
              </Link>
              <button
                onClick={() => {
                  onClose();
                  router.push("/checkout");
                }}
                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full font-semibold text-sm bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all"
              >
                Checkout
                <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
