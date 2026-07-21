"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Package, ChevronRight, Loader2, Eye } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrders } from "@/hooks/use-orders";

const statusColors: Record<string, string> = {
  pending: "bg-amber-100 text-amber-700",
  paid: "bg-emerald-100 text-emerald-700",
  processing: "bg-blue-100 text-blue-700",
  packed: "bg-indigo-100 text-indigo-700",
  shipped: "bg-cyan-100 text-cyan-700",
  delivered: "bg-green-100 text-green-700",
  cancelled: "bg-red-100 text-red-700",
  refunded: "bg-gray-100 text-gray-700",
};

export function OrdersPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: orders, isLoading: ordersLoading } = useOrders();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

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
        title="My Orders"
        subtitle="View and track all your orders"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Orders" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
            {ordersLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-green-500 mx-auto" />
              </div>
            ) : !orders || orders.length === 0 ? (
              <div className="py-16 text-center">
                <Package className="w-14 h-14 mx-auto mb-4 text-[#94a3b8]" />
                <h3 className="text-lg font-bold text-[#0a1628] mb-2">No orders yet</h3>
                <p className="text-[#64748b] mb-6">You haven&apos;t placed any orders yet.</p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
                >
                  Start Shopping
                  <ChevronRight className="w-4 h-4" />
                </Link>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left">
                  <thead>
                    <tr className="border-b border-[#e2e8f0]">
                      <th className="pb-3 font-semibold text-[#0a1628]">Order</th>
                      <th className="pb-3 font-semibold text-[#0a1628]">Date</th>
                      <th className="pb-3 font-semibold text-[#0a1628]">Total</th>
                      <th className="pb-3 font-semibold text-[#0a1628]">Status</th>
                      <th className="pb-3 font-semibold text-[#0a1628]">Payment</th>
                      <th className="pb-3 font-semibold text-[#0a1628] text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {orders.map((order) => (
                      <tr key={order.id} className="border-b border-[#f1f5f9]">
                        <td className="py-4 font-semibold text-[#0a1628]">{order.invoice_number}</td>
                        <td className="py-4 text-[#64748b]">{new Date(order.created_at).toLocaleDateString()}</td>
                        <td className="py-4 font-bold text-[#0d3b66]">UGX {order.total_amount}</td>
                        <td className="py-4">
                          <span
                            className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
                              statusColors[order.status] || "bg-gray-100 text-gray-700"
                            }`}
                          >
                            {order.status}
                          </span>
                        </td>
                        <td className="py-4">
                          <span
                            className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
                              order.payment_status === "paid"
                                ? "bg-emerald-100 text-emerald-700"
                                : order.payment_status === "failed"
                                ? "bg-red-100 text-red-700"
                                : "bg-amber-100 text-amber-700"
                            }`}
                          >
                            {order.payment_status}
                          </span>
                        </td>
                        <td className="py-4 text-right">
                          <Link
                            href={`/account/orders/${order.id}`}
                            className="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-700"
                          >
                            <Eye className="w-4 h-4" />
                            View
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
