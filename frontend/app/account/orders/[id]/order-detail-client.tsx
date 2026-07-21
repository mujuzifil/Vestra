"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Package,
  ChevronLeft,
  Loader2,
  Truck,
  CheckCircle2,
  Clock,
  MapPin,
  CreditCard,
  FileText,
  AlertCircle,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrder } from "@/hooks/use-orders";

const timelineSteps = [
  { status: "pending", label: "Order Placed", icon: Clock },
  { status: "paid", label: "Payment Confirmed", icon: CheckCircle2 },
  { status: "processing", label: "Processing", icon: Package },
  { status: "packed", label: "Packed", icon: Package },
  { status: "shipped", label: "Shipped", icon: Truck },
  { status: "delivered", label: "Delivered", icon: CheckCircle2 },
];

interface Props {
  orderId: number;
}

export function OrderDetailPageClient({ orderId }: Props) {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: order, isLoading: orderLoading } = useOrder(orderId);

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

  const currentStepIndex = timelineSteps.findIndex((s) => s.status === order.status);
  const effectiveIndex = currentStepIndex === -1 ? 0 : currentStepIndex;

  return (
    <>
      <PageHero
        title={`Order ${order.invoice_number}`}
        subtitle="Order details and tracking"
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
                      {timelineSteps.map((step, index) => {
                        const isCompleted = index <= effectiveIndex;
                        const Icon = step.icon;
                        return (
                          <div key={step.status} className="relative flex items-start gap-4">
                            <div
                              className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center ${
                                isCompleted
                                  ? "bg-green-500 text-white"
                                  : "bg-[#e2e8f0] text-[#94a3b8]"
                              }`}
                            >
                              <Icon className="w-4 h-4" />
                            </div>
                            <div className="pt-1">
                              <p
                                className={`font-semibold ${
                                  isCompleted ? "text-[#0a1628]" : "text-[#94a3b8]"
                                }`}
                              >
                                {step.label}
                              </p>
                              {isCompleted && index === effectiveIndex && (
                                <p className="text-sm text-[#64748b]">Current status</p>
                              )}
                            </div>
                          </div>
                        );
                      })}
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
                      className="flex items-center justify-between p-4 rounded-xl bg-[#f8fafc]"
                    >
                      <div>
                        <p className="font-semibold text-[#0a1628]">{item.product_name}</p>
                        <p className="text-sm text-[#64748b]">SKU: {item.product_sku}</p>
                        <p className="text-sm text-[#64748b]">Qty: {item.quantity}</p>
                      </div>
                      <p className="font-bold text-[#0d3b66]">UGX {item.line_total}</p>
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
