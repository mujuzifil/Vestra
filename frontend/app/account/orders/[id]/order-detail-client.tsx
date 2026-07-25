"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import {
  ChevronLeft,
  Loader2,
  Truck,
  Clock,
  MapPin,
  CreditCard,
  FileText,
  AlertCircle,
  RefreshCcw,
  Calendar,
  Search,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrder } from "@/hooks/use-orders";
import { initiatePayment } from "@/lib/api/payments";
import { toastError } from "@/lib/toast-utils";
import type { TimelineEvent } from "@/types";

const statusColors: Record<string, string> = {
  primary: "bg-info-500",
  success: "bg-secondary-500",
  info: "bg-cyan-500",
  warning: "bg-warning-500",
  danger: "bg-danger-500",
  gray: "bg-neutral-400",
};

interface Props {
  orderId: number;
}

function formatDateTime(value: string | null | undefined): string {
  if (!value) return "—";
  return new Date(value).toLocaleString("en-UG", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatDate(value: string | null | undefined): string {
  if (!value) return "—";
  return new Date(value).toLocaleDateString("en-UG", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

export function OrderDetailPageClient({ orderId }: Props) {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: order, isLoading: orderLoading } = useOrder(orderId);
  const [isRetrying, setIsRetrying] = useState(false);

  const canRetryPayment = order &&
    order.payment_status !== "paid" &&
    order.status !== "cancelled" &&
    order.status !== "refunded" &&
    order.payment_method !== "cod";

  async function handleRetryPayment() {
    if (!order) return;
    setIsRetrying(true);
    try {
      const result = await initiatePayment(order.id);
      window.location.href = result.payment_link;
    } catch (err) {
      const message = err instanceof Error ? err.message : "Could not restart payment.";
      toastError(message);
      setIsRetrying(false);
    }
  }

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || orderLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  if (!order) {
    return (
      <Container className="py-20 text-center">
        <AlertCircle className="w-12 h-12 mx-auto mb-4 text-placeholder" />
        <h2 className="text-xl font-bold text-primary-900 mb-2">Order not found</h2>
        <p className="text-muted mb-6">The order you are looking for does not exist.</p>
        <Link
          href="/account/orders"
          className="inline-flex items-center gap-2 text-secondary-600 font-semibold hover:text-secondary-600"
        >
          <ChevronLeft className="w-4 h-4" />
          Back to Orders
        </Link>
      </Container>
    );
  }

  const timeline: TimelineEvent[] = order.timeline?.length
    ? order.timeline
    : [
        {
          icon: "heroicon-o-shopping-cart",
          color: "primary",
          title: "Order created",
          description: `Order #${order.invoice_number} was placed.`,
          time: order.created_at,
          actor: "Customer",
        },
      ];

  return (
    <>
      <PageHero
        title={`Order ${order.invoice_number}`}
        subtitle="Order details, tracking, and invoice"
        breadcrumb={[
          { label: "Account", href: "/account" },
          { label: "Orders", href: "/account/orders" },
          { label: order.invoice_number },
        ]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account/orders"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-primary-900 mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Orders
          </Link>

          {/* Order Metadata */}
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 mb-8">
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">Invoice Number</p>
                <p className="text-lg font-bold text-primary-900">{order.invoice_number}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">Order Date</p>
                <p className="text-base font-semibold text-primary-900">{formatDate(order.created_at)}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">Payment Method</p>
                <p className="text-base font-semibold text-primary-900 capitalize">{order.payment_method.replace(/_/g, " ")}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">Total</p>
                <p className="text-lg font-bold text-primary-500">UGX {order.total_amount}</p>
              </div>
            </div>
            <div className="mt-6 pt-6 border-t border-default flex flex-wrap gap-3">
              <span
                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold capitalize ${
                  order.status === "delivered"
                    ? "bg-secondary-100 text-secondary-600"
                    : order.status === "cancelled" || order.status === "refunded"
                    ? "bg-danger-100 text-danger-600"
                    : order.status === "pending"
                    ? "bg-warning-100 text-warning-600"
                    : "bg-info-100 text-info-600"
                }`}
              >
                {order.status}
              </span>
              <span
                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold capitalize ${
                  order.payment_status === "paid"
                    ? "bg-success-100 text-success-600"
                    : order.payment_status === "failed"
                    ? "bg-danger-100 text-danger-600"
                    : "bg-warning-100 text-warning-600"
                }`}
              >
                Payment: {order.payment_status}
              </span>
            </div>
          </div>

          <div className="grid lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2 space-y-6">
              {/* Order Timeline */}
              {order.status !== "cancelled" && order.status !== "refunded" && (
                <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                  <h2 className="text-lg font-bold text-primary-900 mb-6">Order Timeline</h2>
                  <div className="relative">
                    <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-default" />
                    <div className="space-y-6">
                      {timeline.map((event, index) => {
                        const isLast = index === timeline.length - 1;
                        const colorClass = statusColors[event.color || "gray"] || "bg-neutral-400";
                        return (
                          <div key={index} className="relative flex items-start gap-4">
                            <div
                              className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center ${colorClass} text-white`}
                            >
                              <Clock className="w-4 h-4" />
                            </div>
                            <div className="pt-1 flex-1">
                              <p className="font-semibold text-primary-900">{event.title}</p>
                              <p className="text-sm text-muted">{event.description}</p>
                              <div className="flex items-center gap-2 mt-1 text-xs text-placeholder">
                                <Calendar className="w-3 h-3" />
                                <span>{formatDateTime(event.time)}</span>
                                <span>•</span>
                                <span>{event.actor}</span>
                              </div>
                            </div>
                            {isLast && order.status !== "delivered" && (
                              <span className="text-xs font-semibold text-warning-600 bg-warning-50 px-2 py-1 rounded-full">
                                Current
                              </span>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              )}

              {/* Cancelled / Refunded Message */}
              {(order.status === "cancelled" || order.status === "refunded") && (
                <div className="bg-danger-50 rounded-[20px] border border-danger-200 p-6">
                  <div className="flex items-start gap-3">
                    <AlertCircle className="w-5 h-5 text-danger-600 mt-0.5" />
                    <div>
                      <h2 className="text-lg font-bold text-danger-600 capitalize">Order {order.status}</h2>
                      <p className="text-sm text-danger-600 mt-1">
                        This order has been {order.status}. If you have any questions, please contact support.
                      </p>
                    </div>
                  </div>
                </div>
              )}

              {/* Order Items */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <h2 className="text-lg font-bold text-primary-900 mb-4">Order Items</h2>
                <div className="space-y-4">
                  {order.items.map((item) => {
                    const image = item.product?.images?.[0]?.image || "/assets/images/products/placeholder.png";
                    return (
                      <div
                        key={item.id}
                        className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl bg-surface-page"
                      >
                        <div className="relative w-16 h-16 rounded-lg bg-surface-card overflow-hidden flex-shrink-0">
                          <Image
                            src={image}
                            alt={item.product_name}
                            fill
                            className="object-contain p-2"
                          />
                        </div>
                        <div className="flex-1">
                          <p className="font-semibold text-primary-900">{item.product_name}</p>
                          <p className="text-sm text-muted">SKU: {item.product_sku}</p>
                          <p className="text-sm text-muted">Qty: {item.quantity}</p>
                        </div>
                        <div className="text-left sm:text-right">
                          <p className="font-bold text-primary-500">UGX {item.line_total}</p>
                          <p className="text-sm text-muted">UGX {item.unit_price} each</p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Order Summary */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <h2 className="text-lg font-bold text-primary-900 mb-4">Order Summary</h2>
                <div className="space-y-3">
                  <div className="flex justify-between text-sm">
                    <span className="text-muted">Subtotal</span>
                    <span className="font-medium text-primary-900">UGX {order.subtotal}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-muted">Shipping</span>
                    <span className="font-medium text-primary-900">UGX {order.shipping_cost}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-muted">Tax</span>
                    <span className="font-medium text-primary-900">UGX {order.tax_amount}</span>
                  </div>
                  <div className="pt-3 border-t border-default flex justify-between">
                    <span className="font-bold text-primary-900">Total</span>
                    <span className="font-bold text-primary-500">UGX {order.total_amount}</span>
                  </div>
                </div>
              </div>

              {/* Shipping Address */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <MapPin className="w-5 h-5 text-secondary-600" />
                  <h2 className="text-lg font-bold text-primary-900">Shipping Address</h2>
                </div>
                <div className="text-sm text-muted space-y-1">
                  <p className="font-medium text-primary-900">{order.shipping_address?.full_name}</p>
                  <p>{order.shipping_address?.phone}</p>
                  <p>{order.shipping_address?.address_line}</p>
                  <p>
                    {order.shipping_address?.city}
                    {order.shipping_address?.region ? `, ${order.shipping_address.region}` : ""}
                  </p>
                </div>
              </div>

              {/* Tracking */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <Truck className="w-5 h-5 text-secondary-600" />
                  <h2 className="text-lg font-bold text-primary-900">Tracking</h2>
                </div>
                <div className="text-sm text-muted space-y-2">
                  <div className="flex justify-between">
                    <span>Courier</span>
                    <span className="font-medium text-primary-900">{order.courier || "—"}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Tracking Number</span>
                    <span className="font-medium text-primary-900">{order.tracking_number || "—"}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Dispatched</span>
                    <span className="font-medium text-primary-900">{formatDate(order.dispatched_at)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Estimated Delivery</span>
                    <span className="font-medium text-primary-900">{formatDate(order.estimated_delivery)}</span>
                  </div>
                </div>
                <Link
                  href={`/track?invoice=${encodeURIComponent(order.invoice_number)}`}
                  className="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-secondary-600 bg-secondary-50 hover:bg-secondary-100 transition-colors-base"
                >
                  <Search className="w-4 h-4" />
                  Track Order
                </Link>
              </div>

              {/* Payment Info */}
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <CreditCard className="w-5 h-5 text-secondary-600" />
                  <h2 className="text-lg font-bold text-primary-900">Payment</h2>
                </div>
                <div className="text-sm text-muted space-y-1">
                  <p>
                    Method:{" "}
                    <span className="font-medium text-primary-900 capitalize">
                      {order.payment_method}
                    </span>
                  </p>
                  <p>
                    Status:{" "}
                    <span
                      className={`font-medium capitalize ${
                        order.payment_status === "paid"
                          ? "text-success-600"
                          : order.payment_status === "failed"
                          ? "text-danger-600"
                          : "text-warning-600"
                      }`}
                    >
                      {order.payment_status}
                    </span>
                  </p>
                </div>

                {canRetryPayment && (
                  <button
                    onClick={handleRetryPayment}
                    disabled={isRetrying}
                    className="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-white bg-secondary-600 hover:bg-secondary-600 disabled:opacity-60 disabled:cursor-not-allowed transition-colors-base"
                  >
                    {isRetrying ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <RefreshCcw className="w-4 h-4" />
                    )}
                    {isRetrying ? "Starting Payment..." : "Retry Payment"}
                  </button>
                )}
              </div>

              {/* Invoice Download */}
              <a
                href={`${process.env.NEXT_PUBLIC_API_URL}/orders/${order.id}/invoice`}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center justify-center gap-2 w-full px-4 py-3 bg-primary-900 text-white font-semibold rounded-xl hover:bg-primary-800 transition-colors-base"
              >
                <FileText className="w-4 h-4" />
                Download Invoice
              </a>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
