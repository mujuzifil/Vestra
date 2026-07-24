"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, Activity, ChevronLeft as PrevIcon, ChevronRight as NextIcon } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useActivity } from "@/hooks/use-activity";
import type { ActivityItem } from "@/types";

const ACTIVITY_ICONS: Record<string, string> = {
  login: "🔐",
  password_change: "🔑",
  profile_update: "👤",
  address_added: "📍",
  address_updated: "📍",
  address_deleted: "🗑️",
  order_placed: "🛒",
  order_paid: "💳",
  order_shipped: "🚚",
  order_delivered: "📦",
  preference_update: "⚙️",
  account_deletion_requested: "🚫",
};

function ActivityRow({ item }: { item: ActivityItem }) {
  const icon = ACTIVITY_ICONS[item.type] || "📝";
  return (
    <div className="flex items-start gap-4 p-4 rounded-xl bg-[#f8fafc] border border-[#e2e8f0]">
      <div className="w-10 h-10 rounded-full bg-white border border-[#e2e8f0] flex items-center justify-center text-lg flex-shrink-0">
        {icon}
      </div>
      <div className="flex-1 min-w-0">
        <p className="font-semibold text-[#0a1628]">{item.description}</p>
        <p className="text-sm text-[#64748b]">
          {new Date(item.created_at).toLocaleString()}
          {item.ip_address && ` · IP ${item.ip_address}`}
        </p>
      </div>
    </div>
  );
}

export function ActivityPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const [page, setPage] = useState(1);
  const { data, isLoading } = useActivity(page);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || isLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  const items = data?.data || [];
  const hasPages = data && data.last_page > 1;

  return (
    <>
      <PageHero
        title="Account Activity"
        subtitle="Track recent changes and events on your account"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Activity" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <Link
            href="/account/settings"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Settings
          </Link>

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-green-50 text-green-600">
                <Activity className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-[#0a1628]">Recent Activity</h1>
                <p className="text-sm text-[#64748b]">A timeline of events related to your account.</p>
              </div>
            </div>

            {items.length === 0 ? (
              <div className="py-16 text-center text-[#64748b]">
                <Activity className="w-12 h-12 mx-auto mb-3 text-[#94a3b8]" />
                <p>No activity recorded yet.</p>
              </div>
            ) : (
              <div className="space-y-3">
                {items.map((item) => (
                  <ActivityRow key={item.id} item={item} />
                ))}
              </div>
            )}

            {hasPages && (
              <div className="flex items-center justify-between mt-6 pt-6 border-t border-[#e2e8f0]">
                <button
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page <= 1}
                  className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#e2e8f0] text-sm font-medium text-[#475569] hover:bg-[#f8fafc] disabled:opacity-50"
                >
                  <PrevIcon className="w-4 h-4" />
                  Previous
                </button>
                <span className="text-sm text-[#64748b]">
                  Page {page} of {data.last_page}
                </span>
                <button
                  onClick={() => setPage((p) => Math.min(data.last_page, p + 1))}
                  disabled={page >= data.last_page}
                  className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#e2e8f0] text-sm font-medium text-[#475569] hover:bg-[#f8fafc] disabled:opacity-50"
                >
                  Next
                  <NextIcon className="w-4 h-4" />
                </button>
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
