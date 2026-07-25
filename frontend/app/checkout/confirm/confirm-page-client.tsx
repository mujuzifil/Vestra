"use client";

import { useSearchParams } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { CheckCircle, Package, ArrowRight, CreditCard, MapPin, Home, ShoppingBag, Loader2, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { useOrder } from "@/hooks/use-orders";
import { useAuth } from "@/lib/auth-context";
import { formatPrice, cn } from "@/lib/utils";
import { initiatePayment } from "@/lib/api/payments";
import { toastError } from "@/lib/toast-utils";
import { useState } from "react";

export function ConfirmPageClient() {
  const searchParams = useSearchParams();
  const orderId = searchParams.get("order");
  const { isAuthenticated } = useAuth();
  const { data: order, isLoading, error } = useOrder(orderId ? Number(orderId) : 0);
  const [isInitiating, setIsInitiating] = useState(false);

  if (!orderId) {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-neutral-50 py-12">
        <Container className="max-w-md text-center">
          <AlertCircle className="w-12 h-12 text-warning-500 mx-auto mb-4" />
          <h1 className="text-xl font-bold text-text-heading mb-2">Order not found</h1>
          <p className="text-text-muted mb-6">We could not locate your order details.</p>
          <Link
            href="/products"
            className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-secondary-500 hover:bg-secondary-600 transition-colors-base"
          >
            Continue Shopping
          </Link>
        </Container>
      </div>
    );
  }

  if (isLoading) {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-neutral-50">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-neutral-50 py-12">
        <Container className="max-w-md text-center">
          <AlertCircle className="w-12 h-12 text-danger-500 mx-auto mb-4" />
          <h1 className="text-xl font-bold text-text-heading mb-2">Could not load order</h1>
          <p className="text-text-muted mb-6">{error?.message || "Something went wrong."}</p>
          <Link
            href="/products"
            className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-secondary-500 hover:bg-secondary-600 transition-colors-base"
          >
            Continue Shopping
          </Link>
        </Container>
      </div>
    );
  }

  const shippingAddress = order.shipping_address as Record<string, string>;
  const isDigitalPayment = order.payment_method !== "cod";
  const isPaid = order.payment_status === "paid";

  return (
    <div className="min-h-[calc(100vh-88px)] bg-neutral-50 py-12 lg:py-20">
      <Container className="max-w-3xl">
        <div className="bg-surface-card rounded-[24px] border border-border-default shadow-lg p-8 lg:p-12">
          <div className="text-center mb-8">
            <div className="w-16 h-16 rounded-full bg-secondary-500/10 flex items-center justify-center mx-auto mb-4">
              <CheckCircle className="w-8 h-8 text-secondary-600" />
            </div>
            <h1 className="text-2xl lg:text-3xl font-extrabold text-text-heading mb-2">
              Order Confirmed
            </h1>
            <p className="text-text-muted">
              Thank you for your order. Your order number is{" "}
              <span className="font-bold text-text-heading">{order.invoice_number}</span>.
            </p>
          </div>

          <div className="rounded-[16px] border border-border-default bg-neutral-50 p-6 mb-8">
            <div className="grid sm:grid-cols-2 gap-6">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-text-placeholder mb-1">
                  Order Number
                </p>
                <p className="text-lg font-bold text-text-heading">{order.invoice_number}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-text-placeholder mb-1">
                  Payment Status
                </p>
                <span
                  className={cn(
                    "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold capitalize",
                    isPaid
                      ? "bg-secondary-100 text-secondary-600"
                      : order.payment_status === "failed"
                      ? "bg-danger-100 text-danger-600"
                      : "bg-warning-100 text-warning-600"
                  )}
                >
                  {order.payment_status}
                </span>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-text-placeholder mb-1">
                  Order Status
                </p>
                <p className="text-base font-semibold text-text-heading capitalize">{order.status}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-text-placeholder mb-1">
                  Payment Method
                </p>
                <p className="text-base font-semibold text-text-heading capitalize">
                  {order.payment_method.replace(/_/g, " ")}
                </p>
              </div>
            </div>
          </div>

          <div className="mb-8">
            <h2 className="text-lg font-bold text-text-heading mb-4 flex items-center gap-2">
              <Package className="w-5 h-5 text-secondary-500" />
              Items Ordered
            </h2>
            <div className="space-y-3">
              {order.items.map((item) => {
                const image = item.product?.images?.[0]?.image || "/assets/images/products/placeholder.png";
                return (
                  <div
                    key={item.id}
                    className="flex gap-4 p-4 rounded-xl border border-border-default bg-surface-card"
                  >
                    <div className="relative w-16 h-16 rounded-lg bg-neutral-50 overflow-hidden flex-shrink-0">
                      <Image
                        src={image}
                        alt={item.product_name}
                        fill
                        className="object-contain p-2"
                      />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold text-text-heading">{item.product_name}</p>
                      <p className="text-xs text-text-muted">SKU: {item.product_sku}</p>
                      <p className="text-xs text-text-muted">Qty: {item.quantity}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-semibold text-text-heading">{formatPrice(Number(item.line_total || 0))}</p>
                      <p className="text-xs text-text-muted">
                        {formatPrice(Number(item.unit_price || 0))} each
                      </p>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          <div className="grid sm:grid-cols-2 gap-6 mb-8">
            <div>
              <h2 className="text-lg font-bold text-text-heading mb-3 flex items-center gap-2">
                <MapPin className="w-5 h-5 text-secondary-500" />
                Shipping Address
              </h2>
              <div className="p-4 rounded-xl border border-border-default bg-neutral-50">
                <p className="text-sm text-text-body">{shippingAddress.full_name}</p>
                <p className="text-sm text-text-body">{shippingAddress.phone}</p>
                <p className="text-sm text-text-body">
                  {shippingAddress.address_line}, {shippingAddress.city}
                </p>
                {(shippingAddress.region || shippingAddress.district) && (
                  <p className="text-sm text-text-body">
                    {shippingAddress.region} {shippingAddress.district}
                  </p>
                )}
              </div>
            </div>
            <div>
              <h2 className="text-lg font-bold text-text-heading mb-3 flex items-center gap-2">
                <CreditCard className="w-5 h-5 text-secondary-500" />
                Order Total
              </h2>
              <div className="p-4 rounded-xl border border-border-default bg-neutral-50 space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-text-muted">Subtotal</span>
                  <span className="font-medium">{formatPrice(Number(order.subtotal || 0))}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-text-muted">Shipping</span>
                  <span className="font-medium">{formatPrice(Number(order.shipping_cost || 0))}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-text-muted">Tax</span>
                  <span className="font-medium">{formatPrice(Number(order.tax_amount || 0))}</span>
                </div>
                <div className="flex justify-between text-base font-bold pt-2 border-t border-border-default">
                  <span>Total</span>
                  <span>{formatPrice(Number(order.total_amount || 0))}</span>
                </div>
              </div>
            </div>
          </div>

          <div className="flex flex-col sm:flex-row gap-3">
            <Link
              href="/"
              className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-text-heading bg-surface-card border border-border-default hover:bg-neutral-50 transition-colors-base"
            >
              <Home className="w-4 h-4" />
              Return Home
            </Link>
            <Link
              href="/products"
              className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-text-heading bg-surface-card border border-border-default hover:bg-neutral-50 transition-colors-base"
            >
              <ShoppingBag className="w-4 h-4" />
              Continue Shopping
            </Link>
            {isAuthenticated && (
              <Link
                href="/account/orders"
                className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-0.5 transition-all-base"
              >
                <Package className="w-4 h-4" />
                View My Orders
                <ArrowRight className="w-4 h-4" />
              </Link>
            )}
          </div>

          {isDigitalPayment && !isPaid && (
            <div className="mt-8 p-4 rounded-xl border border-warning-200 bg-warning-50 text-center">
              <p className="text-sm text-warning-600 mb-3">
                Your order is awaiting payment. Complete payment to confirm your order.
              </p>
              <button
                disabled={isInitiating}
                onClick={async () => {
                  if (!order) return;
                  setIsInitiating(true);
                  try {
                    const result = await initiatePayment(order.id);
                    window.location.href = result.payment_link;
                  } catch (err) {
                    const message = err instanceof Error ? err.message : "Could not start payment.";
                    toastError(message);
                    setIsInitiating(false);
                  }
                }}
                className="inline-flex items-center gap-2 px-7 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-primary-500 to-primary-600 hover:from-primary-900 hover:to-primary-600 disabled:opacity-60 disabled:cursor-not-allowed transition-colors-base"
              >
                {isInitiating ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <CreditCard className="w-4 h-4" />
                )}
                {isInitiating ? "Starting Payment..." : "Proceed to Payment"}
                <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          )}
        </div>
      </Container>
    </div>
  );
}
