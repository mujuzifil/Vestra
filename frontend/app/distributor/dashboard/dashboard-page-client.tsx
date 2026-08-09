"use client";

import Link from "next/link";
import {
  ShoppingCart,
  FileSpreadsheet,
  Package,
  Bell,
  CreditCard,
  ArrowRight,
  Loader2,
  AlertCircle,
} from "lucide-react";
import { DistributorStatCard } from "@/components/distributor/distributor-stat-card";
import { CreditLimitCard } from "@/components/distributor/credit-limit-card";
import { DistributorTierBadge } from "@/components/distributor/distributor-tier-badge";
import { useDistributorDashboard } from "@/hooks/use-distributor-dashboard";
import { QuoteStatusBadge } from "@/components/distributor/quote-status-badge";
import type { DistributorOrder, DistributorQuotation, DistributorNotification } from "@/types";

function formatDate(value: string) {
  return new Date(value).toLocaleDateString("en-UG");
}

function OrderRow({ order }: { order: DistributorOrder }) {
  return (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-neutral-50">
      <div>
        <p className="font-semibold text-text-heading">{order.invoice_number}</p>
        <p className="text-sm text-muted">{formatDate(order.created_at)}</p>
      </div>
      <div className="flex items-center gap-3">
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
        <span className="font-bold text-primary-500">UGX {order.total_amount}</span>
      </div>
      <Link
        href={`/distributor/orders/${order.id}`}
        className="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-700"
      >
        View
        <ArrowRight className="w-3 h-3" />
      </Link>
    </div>
  );
}

function QuoteRow({ quote }: { quote: DistributorQuotation }) {
  return (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-neutral-50">
      <div>
        <p className="font-semibold text-text-heading">{quote.reference_number}</p>
        <p className="text-sm text-muted">{formatDate(quote.created_at)}</p>
      </div>
      <div className="flex items-center gap-3">
        <QuoteStatusBadge status={quote.status} />
        <span className="font-bold text-primary-500">UGX {quote.total_amount}</span>
      </div>
      <Link
        href={`/distributor/quotes/${quote.id}`}
        className="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-700"
      >
        View
        <ArrowRight className="w-3 h-3" />
      </Link>
    </div>
  );
}

function NotificationRow({ notification }: { notification: DistributorNotification }) {
  return (
    <div className="flex items-start gap-3 p-4 rounded-xl bg-neutral-50">
      <Bell className={`w-4 h-4 mt-0.5 ${notification.is_read ? "text-placeholder" : "text-green-600"}`} />
      <div className="flex-1">
        <p className={`text-sm font-medium ${notification.is_read ? "text-muted" : "text-text-heading"}`}>
          {notification.title}
        </p>
        <p className="text-xs text-placeholder">{notification.message}</p>
      </div>
      <span className="text-xs text-placeholder whitespace-nowrap">{formatDate(notification.created_at)}</span>
    </div>
  );
}

export function DashboardPageClient() {
  const { data: dashboard, isLoading } = useDistributorDashboard();

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!dashboard) {
    return (
      <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-8 text-center">
        <AlertCircle className="w-10 h-10 mx-auto mb-3 text-placeholder" />
        <h2 className="text-lg font-bold text-text-heading">Could not load dashboard</h2>
        <p className="text-sm text-muted">Please try again later.</p>
      </div>
    );
  }

  const { distributor, stats, recent_orders, recent_quotes, recent_notifications } = dashboard;

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-extrabold text-text-heading">Distributor Dashboard</h1>
          <p className="text-muted">Welcome back, {distributor.company_name}</p>
          <div className="mt-3">
            <DistributorTierBadge tier={distributor.tier} label={distributor.tier_label} size="md" />
          </div>
        </div>
        <div className="flex items-center gap-3">
          <Link
            href="/distributor/quotes/new"
            className="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors-base"
          >
            <FileSpreadsheet className="w-4 h-4" />
            New Quote
          </Link>
          <Link
            href="/distributor/products"
            className="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-neutral-200 text-text-heading font-semibold rounded-xl hover:bg-neutral-50 transition-colors-base"
          >
            <Package className="w-4 h-4" />
            Products
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <DistributorStatCard label="Total Orders" value={stats.total_orders} icon={ShoppingCart} color="bg-primary-900" />
        <DistributorStatCard label="Pending Orders" value={stats.pending_orders} icon={ShoppingCart} color="bg-amber-500" />
        <DistributorStatCard label="Total Quotes" value={stats.total_quotes} icon={FileSpreadsheet} color="bg-blue-500" />
        <DistributorStatCard label="Pending Quotes" value={stats.pending_quotes} icon={FileSpreadsheet} color="bg-indigo-500" />
      </div>

      <div className="grid lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-8">
          <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-lg font-bold text-text-heading">Recent Orders</h2>
              <Link href="/distributor/orders" className="text-sm font-semibold text-green-600 hover:text-green-700">
                View All
              </Link>
            </div>
            {recent_orders.length === 0 ? (
              <p className="text-sm text-muted py-4">No orders yet.</p>
            ) : (
              <div className="space-y-3">{recent_orders.map((order) => <OrderRow key={order.id} order={order} />)}</div>
            )}
          </div>

          <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-lg font-bold text-text-heading">Recent Quotes</h2>
              <Link href="/distributor/quotes" className="text-sm font-semibold text-green-600 hover:text-green-700">
                View All
              </Link>
            </div>
            {recent_quotes.length === 0 ? (
              <p className="text-sm text-muted py-4">No quotes yet.</p>
            ) : (
              <div className="space-y-3">{recent_quotes.map((quote) => <QuoteRow key={quote.id} quote={quote} />)}</div>
            )}
          </div>
        </div>

        <div className="space-y-8">
          <CreditLimitCard credit={distributor.credit_account} />

          <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6">
            <div className="flex items-center gap-3 mb-4">
              <div className="p-2 rounded-xl bg-primary-900">
                <CreditCard className="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 className="text-lg font-bold text-text-heading">Outstanding Balance</h3>
                <p className="text-2xl font-extrabold text-text-heading">UGX {stats.outstanding_balance}</p>
              </div>
            </div>
            <Link
              href="/distributor/payments"
              className="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-green-700 bg-green-50 hover:bg-green-100 transition-colors-base"
            >
              Make Payment
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>

          <div className="bg-white rounded-[20px] border border-neutral-200 shadow-sm p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-lg font-bold text-text-heading">Notifications</h2>
              {stats.unread_notifications > 0 && (
                <span className="px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">
                  {stats.unread_notifications}
                </span>
              )}
            </div>
            {recent_notifications.length === 0 ? (
              <p className="text-sm text-muted py-4">No notifications.</p>
            ) : (
              <div className="space-y-3">
                {recent_notifications.map((notification) => (
                  <NotificationRow key={notification.id} notification={notification} />
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
