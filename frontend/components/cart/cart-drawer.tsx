"use client";

import Link from "next/link";
import Image from "next/image";
import { X, Minus, Plus, ShoppingBag, Trash2, ArrowRight } from "lucide-react";
import { useCartContext } from "@/lib/cart-context";
import { useAuth } from "@/lib/auth-context";
import { formatPrice } from "@/lib/utils";
import { cn } from "@/lib/utils";

export function CartDrawer({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { cart, itemCount, updateItem, removeItem, clear } = useCartContext();
  const { isAuthenticated } = useAuth();

  return (
    <>
      {/* Overlay */}
      <div
        className={cn(
          "fixed inset-0 bg-black/50 z-[60] transition-opacity",
          open ? "opacity-100" : "opacity-0 pointer-events-none"
        )}
        onClick={onClose}
      />
      {/* Drawer */}
      <div
        className={cn(
          "fixed top-0 right-0 h-full w-full max-w-md bg-white z-[70] shadow-2xl transition-transform duration-300 flex flex-col",
          open ? "translate-x-0" : "translate-x-full"
        )}
      >
        <div className="flex items-center justify-between p-5 border-b border-[#e2e8f0]">
          <h2 className="text-lg font-bold text-[#0a1628] flex items-center gap-2">
            <ShoppingBag className="w-5 h-5" />
            Your Cart ({itemCount})
          </h2>
          <button onClick={onClose} className="p-2 rounded-full hover:bg-[#f8fafc] transition-colors">
            <X className="w-5 h-5 text-[#64748b]" />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-5">
          {!cart || cart.items.length === 0 ? (
            <div className="text-center py-12">
              <ShoppingBag className="w-12 h-12 text-[#94a3b8] mx-auto mb-4" />
              <p className="text-[#64748b] mb-4">Your cart is empty</p>
              <Link
                href="/products"
                onClick={onClose}
                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm bg-green-500 text-white hover:bg-green-600 transition-colors"
              >
                Start Shopping
              </Link>
            </div>
          ) : (
            <div className="space-y-4">
              {cart.items.map((item) => (
                <div key={item.id} className="flex gap-4 p-3 rounded-xl bg-[#f8fafc]">
                  <div className="relative w-20 h-20 rounded-lg bg-white overflow-hidden flex-shrink-0">
                    <Image
                      src={item.product?.images?.[0]?.image || "/assets/images/products/placeholder.png"}
                      alt={item.product?.name || "Product"}
                      fill
                      className="object-contain p-2"
                    />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-[#0a1628] text-sm truncate">{item.product?.name || "Product"}</p>
                    <p className="text-xs text-[#64748b] mt-0.5">UGX {formatPrice(Number(item.product?.price || 0))}</p>
                    <div className="flex items-center gap-2 mt-2">
                      <button
                        onClick={() => updateItem(item.id, Math.max(1, item.quantity - 1))}
                        className="w-7 h-7 rounded-full bg-white border border-[#e2e8f0] flex items-center justify-center hover:border-green-500 transition-colors"
                      >
                        <Minus className="w-3 h-3" />
                      </button>
                      <span className="text-sm font-semibold w-6 text-center">{item.quantity}</span>
                      <button
                        onClick={() => updateItem(item.id, item.quantity + 1)}
                        className="w-7 h-7 rounded-full bg-white border border-[#e2e8f0] flex items-center justify-center hover:border-green-500 transition-colors"
                      >
                        <Plus className="w-3 h-3" />
                      </button>
                      <button
                        onClick={() => removeItem(item.id)}
                        className="ml-auto p-1.5 text-[#94a3b8] hover:text-red-500 transition-colors"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {cart && cart.items.length > 0 && (
          <div className="p-5 border-t border-[#e2e8f0] space-y-4">
            <div className="flex items-center justify-between text-sm">
              <span className="text-[#64748b]">Subtotal</span>
              <span className="font-bold text-[#0a1628]">UGX {cart.subtotal}</span>
            </div>
            <div className="flex gap-3">
              <button
                onClick={() => clear()}
                className="px-4 py-2.5 rounded-full text-sm font-semibold text-[#64748b] border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
              >
                Clear
              </button>
              <Link
                href={isAuthenticated ? "/checkout" : "/auth/login"}
                onClick={onClose}
                className="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-semibold text-sm bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all"
              >
                Checkout
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
