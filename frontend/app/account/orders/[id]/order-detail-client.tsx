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
  primary: "bg-blue-500",
  success: "bg-green-500",
  info: "bg-cyan-500",
  warning: "bg-amber-500",
  danger: "bg-red-500",
  gray: "bg-gray-400",
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
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  if (!order) {
    return (
      <Container className="py-20 text-center">
        <AlertCircle className="w-12 h-12 mx-auto mb-4 text-[#94a3b8]" />
        <h2 className="text-xl font-bold text-[#0a1628] mb-2">Order not found</h2>
        <p className="text-[#64748b] mb-6">The order you are looking for does not exist.</p>
        <Link
          href="/account/orders"
          className="inline-flex items-center gap-2 text-green-600 font-semibold hover:text-green-700"
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

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <Link
            href="/account/orders"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Orders
          </Link>

          {/* Order Metadata */}
          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 mb-8">
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-[#94a3b8] mb-1">Invoice Number</p>
                <p className="text-lg font-bold text-[#0a1628]">{order.invoice_number}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-[#94a3b8] mb-1">Order Date</p>
                <p className="text-base font-semibold text-[#0a1628]">{formatDate(order.created_at)}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-[#94a3b8] mb-1">Payment Method</p>
                <p className="text-base font-semibold text-[#0a1628] capitalize">{order.payment_method.replace(/_/g, " ")}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-[#94a3b8] mb-1">Total</p>
                <p className="text-lg font-bold text-[#0d3b66]">UGX {order.total_amount}</p>
              </div>
            </div>
            <div className="mt-6 pt-6 border-t border-[#e2e8f0] flex flex-wrap gap-3">
              <span
                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold capitalize ${
                  order.status === "delivered"
                    ? "bg-green-100 text-green-700"
                    : order.status === "cancelled" || order.status === "refunded"
                    ? "bg-red-100 text-red-700"
                    : order.status === "pending"
                    ? "bg-amber-100 text-amber-700"
                    : "bg-blue-100 text-blue-700"
                }`}
              >
                {order.status}
              </span>
              <span
                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold capitalize ${
                  order.payment_status === "paid"
                    ? "bg-emerald-100 text-emerald-700"
                    : order.payment_status === "failed"
                    ? "bg-red-100 text-red-700"
                    : "bg-amber-100 text-amber-700"
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
                <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                  <h2 className="text-lg font-bold text-[#0a1628] mb-6">Order Timeline</h2>
                  <div className="relative">
                    <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-[#e2e8f0]" />
                    <div className="space-y-6">
                      {timeline.map((event, index) => {
                        const isLast = index === timeline.length - 1;
                        const colorClass = statusColors[event.color || "gray"] || "bg-gray-400";
                        return (
                          <div key={index} className="relative flex items-start gap-4">
                            <div
                              className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center ${colorClass} text-white`}
                            >
                              <Clock className="w-4 h-4" />
                            </div>
                            <div className="pt-1 flex-1">
                              <p className="font-semibold text-[#0a1628]">{event.title}</p>
                              <p className="text-sm text-[#64748b]">{event.description}</p>
                              <div className="flex items-center gap-2 mt-1 text-xs text-[#94a3b8]">
                                <Calendar className="w-3 h-3" />
                                <span>{formatDateTime(event.time)}</span>
                                <span>•</span>
                                <span>{event.actor}</span>
                              </div>
                            </div>
                            {isLast && order.status !== "delivered" && (
                              <span className="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
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
                <div className="bg-red-50 rounded-[20px] border border-red-200 p-6">
                  <div className="flex items-start gap-3">
                    <AlertCircle className="w-5 h-5 text-red-600 mt-0.5" />
                    <div>
                      <h2 className="text-lg font-bold text-red-800 capitalize">Order {order.status}</h2>
                      <p className="text-sm text-red-700 mt-1">
                        This order has been {order.status}. If you have any questions, please contact support.
                      </p>
                    </div>
                  </div>
                </div>
              )}

              {/* Order Items */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Order Items</h2>
                <div className="space-y-4">
                  {order.items.map((item) => (
                    <div
                      key={item.id}
                      className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl bg-[#f8fafc]"
                    >
                      <div className="relative w-16 h-16 rounded-lg bg-white overflow-hidden flex-shrink-0">
                        <Image
                          src="/assets/images/products/placeholder.png"
                          alt={item.product_name}
                          fill
                          className="object-contain p-2"
                        />
                      </div>
                      <div className="flex-1">
                        <p className="font-semibold text-[#0a1628]">{item.product_name}</p>
                        <p className="text-sm text-[#64748b]">SKU: {item.product_sku}</p>
                        <p className="text-sm text-[#64748b]">Qty: {item.quantity}</p>
                      </div>
                      <div className="text-left sm:text-right">
                        <p className="font-bold text-[#0d3b66]">UGX {item.line_total}</p>
                        <p className="text-sm text-[#64748b]">UGX {item.unit_price} each</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              {/* Order Summary */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Order Summary</h2>
                <div className="space-y-3">
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b]">Subtotal</span>
                    <span className="font-medium text-[#0a1628]">UGX {order.subtotal}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b]">Shipping</span>
                    <span className="font-medium text-[#0a1628]">UGX {order.shipping_cost}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-[#64748b]">Tax</span>
                    <span className="font-medium text-[#0a1628]">UGX {order.tax_amount}</span>
                  </div>
                  <div className="pt-3 border-t border-[#e2e8f0] flex justify-between">
                    <span className="font-bold text-[#0a1628]">Total</span>
                    <span className="font-bold text-[#0d3b66]">UGX {order.total_amount}</span>
                  </div>
                </div>
              </div>

              {/* Shipping Address */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <MapPin className="w-5 h-5 text-green-600" />
                  <h2 className="text-lg font-bold text-[#0a1628]">Shipping Address</h2>
                </div>
                <div className="text-sm text-[#64748b] space-y-1">
                  <p className="font-medium text-[#0a1628]">{order.shipping_address?.full_name}</p>
                  <p>{order.shipping_address?.phone}</p>
                  <p>{order.shipping_address?.address_line}</p>
                  <p>
                    {order.shipping_address?.city}
                    {order.shipping_address?.region ? `, ${order.shipping_address.region}` : ""}
                  </p>
                </div>
              </div>

              {/* Tracking */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <Truck className="w-5 h-5 text-green-600" />
                  <h2 className="text-lg font-bold text-[#0a1628]">Tracking</h2>
                </div>
                <div className="text-sm text-[#64748b] space-y-2">
                  <div className="flex justify-between">
                    <span>Courier</span>
                    <span className="font-medium text-[#0a1628]">{order.courier || "—"}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Tracking Number</span>
                    <span className="font-medium text-[#0a1628]">{order.tracking_number || "—"}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Dispatched</span>
                    <span className="font-medium text-[#0a1628]">{formatDate(order.dispatched_at)}</span>
                  </div>
                  <div className="flex justify-between">
                    <span>Estimated Delivery</span>
                    <span className="font-medium text-[#0a1628]">{formatDate(order.estimated_delivery)}</span>
                  </div>
                </div>
                <Link
                  href={`/track?invoice=${encodeURIComponent(order.invoice_number)}`}
                  className="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-green-700 bg-green-50 hover:bg-green-100 transition-colors"
                >
                  <Search className="w-4 h-4" />
                  Track Order
                </Link>
              </div>

              {/* Payment Info */}
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
                <div className="flex items-center gap-2 mb-4">
                  <CreditCard className="w-5 h-5 text-green-600" />
                  <h2 className="text-lg font-bold text-[#0a1628]">Payment</h2>
                </div>
                <div className="text-sm text-[#64748b] space-y-1">
                  <p>
                    Method:{" "}
                    <span className="font-medium text-[#0a1628] capitalize">
                      {order.payment_method}
                    </span>
                  </p>
                  <p>
                    Status:{" "}
                    <span
                      className={`font-medium capitalize ${
                        order.payment_status === "paid"
                          ? "text-emerald-600"
                          : order.payment_status === "failed"
                          ? "text-red-600"
                          : "text-amber-600"
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
                    className="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-white bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
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
                href={`${process.env.NEXT_PUBLIC_API_URL}/api/v1/orders/${order.id}/invoice`}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center justify-center gap-2 w-full px-4 py-3 bg-[#0a1628] text-white font-semibold rounded-xl hover:bg-[#1a2638] transition-colors"
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
