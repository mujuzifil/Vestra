"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Package,
  Search,
  Loader2,
  AlertCircle,
  Truck,
  MapPin,
  Calendar,
  Clock,
  ChevronLeft,
  CheckCircle,
  Box,
  TruckIcon,
  XCircle,
  RotateCcw,
  CheckCircle2,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrders } from "@/hooks/use-orders";
import type { Order, TimelineEvent } from "@/types";

interface Props {
  initialInvoice?: string;
}

const statusIcons: Record<string, React.ElementType> = {
  pending: Clock,
  paid: CheckCircle2,
  processing: Package,
  packed: Box,
  shipped: TruckIcon,
  delivered: CheckCircle,
  cancelled: XCircle,
  refunded: RotateCcw,
};

const statusColors: Record<string, string> = {
  primary: "bg-blue-500",
  success: "bg-green-500",
  info: "bg-cyan-500",
  warning: "bg-amber-500",
  danger: "bg-red-500",
  gray: "bg-gray-400",
};

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

function TrackingResult({ order }: { order: Order }) {
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

  const latestEvent = timeline[timeline.length - 1];

  return (
    <div className="space-y-6">
      {/* Status Header */}
      <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <p className="text-sm text-[#64748b] mb-1">Order {order.invoice_number}</p>
            <h2 className="text-2xl font-extrabold text-[#0a1628] capitalize">{order.status}</h2>
            <p className="text-[#64748b] mt-1">{latestEvent?.description}</p>
          </div>
          <div className="text-left md:text-right">
            <p className="text-sm text-[#64748b]">Estimated Delivery</p>
            <p className="text-xl font-bold text-[#0d3b66]">{formatDate(order.estimated_delivery)}</p>
          </div>
        </div>
      </div>

      <div className="grid lg:grid-cols-3 gap-6">
        {/* Timeline */}
        <div className="lg:col-span-2 bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
          <h3 className="text-lg font-bold text-[#0a1628] mb-6">Shipment Timeline</h3>
          <div className="relative">
            <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-[#e2e8f0]" />
            <div className="space-y-6">
              {timeline.map((event, index) => {
                const Icon = statusIcons[event.title.toLowerCase().split(" ")[0]] || Clock;
                const colorClass = statusColors[event.color || "gray"] || "bg-gray-400";
                return (
                  <div key={index} className="relative flex items-start gap-4">
                    <div
                      className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center ${colorClass} text-white`}
                    >
                      <Icon className="w-4 h-4" />
                    </div>
                    <div className="pt-1 flex-1">
                      <p className="font-semibold text-[#0a1628]">{event.title}</p>
                      <p className="text-sm text-[#64748b]">{event.description}</p>
                      <div className="flex items-center gap-2 mt-1 text-xs text-[#94a3b8]">
                        <Calendar className="w-3 h-3" />
                        <span>{formatDateTime(event.time)}</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        {/* Details */}
        <div className="space-y-6">
          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
            <div className="flex items-center gap-2 mb-4">
              <Truck className="w-5 h-5 text-green-600" />
              <h3 className="text-lg font-bold text-[#0a1628]">Tracking Details</h3>
            </div>
            <div className="text-sm text-[#64748b] space-y-2">
              <div className="flex justify-between">
                <span>Courier</span>
                <span className="font-medium text-[#0a1628]">{order.courier || "—"}</span>
              </div>
              <div className="flex justify-between">
                <span>Tracking #</span>
                <span className="font-medium text-[#0a1628]">{order.tracking_number || "—"}</span>
              </div>
              <div className="flex justify-between">
                <span>Dispatched</span>
                <span className="font-medium text-[#0a1628]">{formatDate(order.dispatched_at)}</span>
              </div>
              <div className="flex justify-between">
                <span>Delivered</span>
                <span className="font-medium text-[#0a1628]">{formatDate(order.delivered_at)}</span>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
            <div className="flex items-center gap-2 mb-4">
              <MapPin className="w-5 h-5 text-green-600" />
              <h3 className="text-lg font-bold text-[#0a1628]">Shipping Address</h3>
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

          <Link
            href={`/account/orders/${order.id}`}
            className="flex items-center justify-center gap-2 w-full px-4 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
          >
            <Package className="w-4 h-4" />
            View Full Order
          </Link>
        </div>
      </div>
    </div>
  );
}

export function TrackPageClient({ initialInvoice = "" }: Props) {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: orders, isLoading: ordersLoading } = useOrders();

  const [query, setQuery] = useState(initialInvoice);
  const [submittedQuery, setSubmittedQuery] = useState(initialInvoice);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const matchedOrder = useMemo(() => {
    if (!orders || !submittedQuery.trim()) return null;
    const term = submittedQuery.trim().toLowerCase();
    return orders.find(
      (o) =>
        o.invoice_number.toLowerCase() === term ||
        String(o.id) === term ||
        (o.tracking_number && o.tracking_number.toLowerCase() === term)
    );
  }, [orders, submittedQuery]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="Track Your Order"
        subtitle="Enter your invoice number or order ID to see the latest status"
        breadcrumb={[{ label: "Track Order" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container className="max-w-4xl">
          <Link
            href="/account"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Account
          </Link>

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 mb-8">
            <form
              onSubmit={(e) => {
                e.preventDefault();
                setSubmittedQuery(query);
              }}
              className="flex flex-col sm:flex-row gap-3"
            >
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
                <input
                  type="text"
                  placeholder="Invoice number or order ID"
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-3 rounded-xl border border-[#e2e8f0] focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              <button
                type="submit"
                disabled={ordersLoading}
                className="inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
              >
                {ordersLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Truck className="w-4 h-4" />}
                Track Order
              </button>
            </form>
          </div>

          {ordersLoading ? (
            <div className="py-12 text-center">
              <Loader2 className="w-8 h-8 animate-spin text-green-500 mx-auto" />
            </div>
          ) : submittedQuery && !matchedOrder ? (
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-12 text-center">
              <AlertCircle className="w-14 h-14 mx-auto mb-4 text-[#94a3b8]" />
              <h3 className="text-lg font-bold text-[#0a1628] mb-2">Order not found</h3>
              <p className="text-[#64748b] mb-6">
                We couldn&apos;t find an order matching &quot;{submittedQuery}&quot;. Please check the number and try again.
              </p>
              <Link
                href="/account/orders"
                className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
              >
                <Package className="w-4 h-4" />
                View My Orders
              </Link>
            </div>
          ) : matchedOrder ? (
            <TrackingResult order={matchedOrder} />
          ) : null}
        </Container>
      </section>
    </>
  );
}
