"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Package,
  ChevronRight,
  Loader2,
  Eye,
  Search,
  SlidersHorizontal,
  ArrowUpDown,
  CreditCard,
  ShoppingBag,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useOrders } from "@/hooks/use-orders";
import type { Order } from "@/types";

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

const statusOptions = [
  { value: "", label: "All Statuses" },
  { value: "pending", label: "Pending" },
  { value: "paid", label: "Paid" },
  { value: "processing", label: "Processing" },
  { value: "packed", label: "Packed" },
  { value: "shipped", label: "Shipped" },
  { value: "delivered", label: "Delivered" },
  { value: "cancelled", label: "Cancelled" },
  { value: "refunded", label: "Refunded" },
];

const paymentStatusOptions = [
  { value: "", label: "All Payments" },
  { value: "paid", label: "Paid" },
  { value: "pending", label: "Pending" },
  { value: "failed", label: "Failed" },
  { value: "refunded", label: "Refunded" },
];

const sortOptions = [
  { value: "date-desc", label: "Newest First" },
  { value: "date-asc", label: "Oldest First" },
  { value: "total-desc", label: "Highest Total" },
  { value: "total-asc", label: "Lowest Total" },
];

const ITEMS_PER_PAGE = 10;

function OrderStatusBadge({ status }: { status: string }) {
  return (
    <span
      className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
        statusColors[status] || "bg-gray-100 text-gray-700"
      }`}
    >
      {status}
    </span>
  );
}

function PaymentStatusBadge({ status }: { status: string }) {
  return (
    <span
      className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
        status === "paid"
          ? "bg-emerald-100 text-emerald-700"
          : status === "failed"
          ? "bg-red-100 text-red-700"
          : "bg-amber-100 text-amber-700"
      }`}
    >
      {status}
    </span>
  );
}

function OrderCard({ order }: { order: Order }) {
  return (
    <div className="bg-[#f8fafc] rounded-xl p-4 sm:hidden">
      <div className="flex items-start justify-between mb-2">
        <div>
          <p className="font-semibold text-[#0a1628]">{order.invoice_number}</p>
          <p className="text-xs text-[#64748b]">{new Date(order.created_at).toLocaleDateString()}</p>
        </div>
        <OrderStatusBadge status={order.status} />
      </div>
      <div className="flex items-center justify-between mb-3">
        <p className="font-bold text-[#0d3b66]">UGX {order.total_amount}</p>
        <PaymentStatusBadge status={order.payment_status} />
      </div>
      <Link
        href={`/account/orders/${order.id}`}
        className="w-full inline-flex items-center justify-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-700 bg-white border border-[#e2e8f0] rounded-lg py-2"
      >
        <Eye className="w-4 h-4" />
        View Order
      </Link>
    </div>
  );
}

export function OrdersPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: orders, isLoading: ordersLoading } = useOrders();

  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [paymentFilter, setPaymentFilter] = useState("");
  const [sort, setSort] = useState("date-desc");
  const [currentPage, setCurrentPage] = useState(1);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const stats = useMemo(() => {
    const all = orders || [];
    return {
      total: all.length,
      pendingPayment: all.filter((o) => o.payment_status !== "paid" && o.status !== "cancelled" && o.status !== "refunded").length,
      processing: all.filter((o) => ["paid", "processing", "packed", "shipped"].includes(o.status)).length,
      completed: all.filter((o) => o.status === "delivered").length,
      cancelled: all.filter((o) => o.status === "cancelled" || o.status === "refunded").length,
    };
  }, [orders]);

  const filteredOrders = useMemo(() => {
    let result = orders ? [...orders] : [];

    if (search.trim()) {
      const term = search.toLowerCase();
      result = result.filter(
        (o) =>
          o.invoice_number.toLowerCase().includes(term) ||
          o.status.toLowerCase().includes(term)
      );
    }

    if (statusFilter) {
      result = result.filter((o) => o.status === statusFilter);
    }

    if (paymentFilter) {
      result = result.filter((o) => o.payment_status === paymentFilter);
    }

    result.sort((a, b) => {
      switch (sort) {
        case "date-asc":
          return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
        case "total-desc":
          return Number(b.total_amount.replace(/,/g, "")) - Number(a.total_amount.replace(/,/g, ""));
        case "total-asc":
          return Number(a.total_amount.replace(/,/g, "")) - Number(b.total_amount.replace(/,/g, ""));
        case "date-desc":
        default:
          return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
      }
    });

    return result;
  }, [orders, search, statusFilter, paymentFilter, sort]);

  const totalPages = Math.max(1, Math.ceil(filteredOrders.length / ITEMS_PER_PAGE));
  const paginatedOrders = filteredOrders.slice(
    (currentPage - 1) * ITEMS_PER_PAGE,
    currentPage * ITEMS_PER_PAGE
  );

  useEffect(() => {
    setCurrentPage(1);
  }, [search, statusFilter, paymentFilter, sort]);

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
        subtitle="View, track, and manage all your orders"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Orders" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          {/* Stats */}
          <div className="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-4">
              <p className="text-sm text-[#64748b]">Total Orders</p>
              <p className="text-2xl font-extrabold text-[#0a1628]">{stats.total}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-4">
              <p className="text-sm text-[#64748b]">Pending Payment</p>
              <p className="text-2xl font-extrabold text-amber-600">{stats.pendingPayment}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-4">
              <p className="text-sm text-[#64748b]">Processing</p>
              <p className="text-2xl font-extrabold text-blue-600">{stats.processing}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-4">
              <p className="text-sm text-[#64748b]">Completed</p>
              <p className="text-2xl font-extrabold text-green-600">{stats.completed}</p>
            </div>
            <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-4 col-span-2 lg:col-span-1">
              <p className="text-sm text-[#64748b]">Cancelled / Refunded</p>
              <p className="text-2xl font-extrabold text-red-600">{stats.cancelled}</p>
            </div>
          </div>

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
            {/* Filters */}
            <div className="flex flex-col lg:flex-row gap-3 mb-6">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
                <input
                  type="text"
                  placeholder="Search by invoice number..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              <div className="flex gap-3">
                <div className="relative">
                  <SlidersHorizontal className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
                  <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                  >
                    {statusOptions.map((o) => (
                      <option key={o.value} value={o.value}>
                        {o.label}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="relative">
                  <CreditCard className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
                  <select
                    value={paymentFilter}
                    onChange={(e) => setPaymentFilter(e.target.value)}
                    className="pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                  >
                    {paymentStatusOptions.map((o) => (
                      <option key={o.value} value={o.value}>
                        {o.label}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="relative">
                  <ArrowUpDown className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
                  <select
                    value={sort}
                    onChange={(e) => setSort(e.target.value)}
                    className="pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white"
                  >
                    {sortOptions.map((o) => (
                      <option key={o.value} value={o.value}>
                        {o.label}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            </div>

            {ordersLoading ? (
              <div className="py-12 text-center">
                <Loader2 className="w-8 h-8 animate-spin text-green-500 mx-auto" />
              </div>
            ) : filteredOrders.length === 0 ? (
              <div className="py-16 text-center">
                <Package className="w-14 h-14 mx-auto mb-4 text-[#94a3b8]" />
                <h3 className="text-lg font-bold text-[#0a1628] mb-2">
                  {orders?.length === 0 ? "No orders yet" : "No matching orders"}
                </h3>
                <p className="text-[#64748b] mb-6">
                  {orders?.length === 0
                    ? "You haven't placed any orders yet."
                    : "Try adjusting your search or filters."}
                </p>
                <Link
                  href="/products"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors"
                >
                  <ShoppingBag className="w-4 h-4" />
                  Start Shopping
                  <ChevronRight className="w-4 h-4" />
                </Link>
              </div>
            ) : (
              <>
                {/* Desktop Table */}
                <div className="hidden sm:overflow-x-auto sm:block">
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
                      {paginatedOrders.map((order) => (
                        <tr key={order.id} className="border-b border-[#f1f5f9]">
                          <td className="py-4 font-semibold text-[#0a1628]">{order.invoice_number}</td>
                          <td className="py-4 text-[#64748b]">{new Date(order.created_at).toLocaleDateString()}</td>
                          <td className="py-4 font-bold text-[#0d3b66]">UGX {order.total_amount}</td>
                          <td className="py-4">
                            <OrderStatusBadge status={order.status} />
                          </td>
                          <td className="py-4">
                            <PaymentStatusBadge status={order.payment_status} />
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

                {/* Mobile Cards */}
                <div className="sm:hidden space-y-3">
                  {paginatedOrders.map((order) => (
                    <OrderCard key={order.id} order={order} />
                  ))}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                  <div className="flex items-center justify-between mt-6 pt-6 border-t border-[#e2e8f0]">
                    <button
                      onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                      disabled={currentPage === 1}
                      className="px-4 py-2 text-sm font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#f8fafc]"
                    >
                      Previous
                    </button>
                    <p className="text-sm text-[#64748b]">
                      Page {currentPage} of {totalPages}
                    </p>
                    <button
                      onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                      disabled={currentPage === totalPages}
                      className="px-4 py-2 text-sm font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#f8fafc]"
                    >
                      Next
                    </button>
                  </div>
                )}
              </>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
