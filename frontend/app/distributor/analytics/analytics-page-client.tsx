"use client";

import { Loader2, TrendingUp, ShoppingCart, FileSpreadsheet, Package } from "lucide-react";
import { DistributorStatCard } from "@/components/distributor/distributor-stat-card";
import { useDistributorAnalytics } from "@/hooks/use-distributor-analytics";
import { EmptyState } from "@/components/common/empty-state";

function SimpleBarChart({ data }: { data: { label: string; value: number }[] }) {
  const max = Math.max(...data.map((d) => d.value), 1);
  return (
    <div className="space-y-3">
      {data.map((item) => (
        <div key={item.label} className="space-y-1">
          <div className="flex justify-between text-sm">
            <span className="text-[#64748b]">{item.label}</span>
            <span className="font-semibold text-[#0a1628]">{item.value.toLocaleString()}</span>
          </div>
          <div className="h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
            <div
              className="h-full bg-green-600 rounded-full"
              style={{ width: `${(item.value / max) * 100}%` }}
            />
          </div>
        </div>
      ))}
    </div>
  );
}

export function AnalyticsPageClient() {
  const { data: analytics, isLoading } = useDistributorAnalytics();

  if (isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!analytics) {
    return <EmptyState title="No analytics data" description="Check back once you have order history." />;
  }

  const statusData = Object.entries(analytics.orders_by_status).map(([label, value]) => ({ label, value }));
  const topProducts = analytics.top_products.map((p) => ({ label: p.product_name, value: p.total_quantity }));
  const revenueData = analytics.revenue_by_month.map((r) => ({
    label: r.month,
    value: Number(r.revenue),
  }));

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Analytics</h1>
        <p className="text-[#64748b]">Insights into your distributor performance.</p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <DistributorStatCard label="Total Orders" value={analytics.total_orders} icon={ShoppingCart} color="bg-[#0a1628]" />
        <DistributorStatCard label="Total Revenue" value={`UGX ${Number(analytics.total_revenue).toLocaleString()}`} icon={TrendingUp} color="bg-green-600" />
        <DistributorStatCard label="Total Quotes" value={analytics.total_quotes} icon={FileSpreadsheet} color="bg-blue-500" />
        <DistributorStatCard label="Avg Order Value" value={`UGX ${Number(analytics.average_order_value).toLocaleString()}`} icon={Package} color="bg-indigo-500" />
      </div>

      <div className="grid lg:grid-cols-2 gap-8">
        <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
          <h2 className="text-lg font-bold text-[#0a1628] mb-4">Revenue by Month</h2>
          {revenueData.length === 0 ? (
            <p className="text-sm text-[#64748b]">No revenue data yet.</p>
          ) : (
            <SimpleBarChart data={revenueData} />
          )}
        </div>

        <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
          <h2 className="text-lg font-bold text-[#0a1628] mb-4">Orders by Status</h2>
          {statusData.length === 0 ? (
            <p className="text-sm text-[#64748b]">No order status data.</p>
          ) : (
            <SimpleBarChart data={statusData} />
          )}
        </div>
      </div>

      <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6">
        <h2 className="text-lg font-bold text-[#0a1628] mb-4">Top Products</h2>
        {topProducts.length === 0 ? (
          <p className="text-sm text-[#64748b]">No product sales data yet.</p>
        ) : (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {analytics.top_products.map((product) => (
              <div key={product.product_id} className="p-4 rounded-xl bg-[#f8fafc]">
                <p className="font-semibold text-[#0a1628] line-clamp-1">{product.product_name}</p>
                <div className="flex justify-between text-sm mt-2">
                  <span className="text-[#64748b]">Qty: {product.total_quantity}</span>
                  <span className="font-medium text-green-600">UGX {product.total_revenue}</span>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
