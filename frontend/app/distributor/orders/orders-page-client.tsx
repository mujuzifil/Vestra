"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { Search, Loader2, ArrowRight, SlidersHorizontal } from "lucide-react";
import { useDistributorOrders } from "@/hooks/use-distributor-orders";
import { EmptyState } from "@/components/common/empty-state";
import type { DistributorOrder } from "@/types";

const statusOptions = [
  { value: "", label: "All Statuses" },
  { value: "pending", label: "Pending" },
  { value: "paid", label: "Paid" },
  { value: "processing", label: "Processing" },
  { value: "shipped", label: "Shipped" },
  { value: "delivered", label: "Delivered" },
  { value: "cancelled", label: "Cancelled" },
];

function OrderCard({ order }: { order: DistributorOrder }) {
  return (
    <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-5">
      <div className="flex items-start justify-between mb-3">
        <div>
          <p className="font-bold text-[#0a1628]">{order.invoice_number}</p>
          <p className="text-xs text-[#64748b]">{new Date(order.created_at).toLocaleDateString()}</p>
        </div>
        <span
          className={`inline-block px-2.5 py-0.5 rounded-full text-xs font-medium capitalize ${
            order.status === "delivered"
              ? "bg-green-100 text-green-700"
              : order.status === "cancelled" || order.status === "refunded"
              ? "bg-red-100 text-red-700"
              : "bg-blue-100 text-blue-700"
          }`}
        >
          {order.status}
        </span>
      </div>
      <p className="text-sm text-[#64748b] mb-4">Payment: <span className="capitalize">{order.payment_status}</span></p>
      <div className="flex items-center justify-between">
        <span className="font-extrabold text-[#0a1628]">UGX {order.total_amount}</span>
        <Link
          href={`/distributor/orders/${order.id}`}
          className="inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-700"
        >
          View
          <ArrowRight className="w-3 h-3" />
        </Link>
      </div>
    </div>
  );
}

export function OrdersPageClient() {
  const { data: orders, isLoading } = useDistributorOrders();
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");

  const filtered = useMemo(() => {
    let result = orders ? [...orders] : [];
    const term = search.toLowerCase().trim();
    if (term) {
      result = result.filter((o) => o.invoice_number.toLowerCase().includes(term));
    }
    if (statusFilter) {
      result = result.filter((o) => o.status === statusFilter);
    }
    return result.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
  }, [orders, search, statusFilter]);

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Orders</h1>
        <p className="text-[#64748b]">Track and manage your distributor orders.</p>
      </div>

      <div className="flex flex-col sm:flex-row gap-3">
        <div className="relative flex-1 max-w-sm">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94a3b8]" />
          <input
            type="text"
            placeholder="Search orders..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#e2e8f0] text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>
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
      </div>

      {isLoading ? (
        <div className="min-h-[50vh] flex items-center justify-center">
          <Loader2 className="w-8 h-8 animate-spin text-green-500" />
        </div>
      ) : filtered.length === 0 ? (
        <EmptyState title="No orders found" description="Your distributor orders will appear here." />
      ) : (
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {filtered.map((order) => (
            <OrderCard key={order.id} order={order} />
          ))}
        </div>
      )}
    </div>
  );
}
