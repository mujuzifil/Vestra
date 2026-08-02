"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  ChevronLeft,
  Loader2,
  Activity,
  ChevronLeft as PrevIcon,
  ChevronRight as NextIcon,
  LogIn,
  KeyRound,
  UserCog,
  MapPin,
  FileText,
  SlidersHorizontal,
  Trash2,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useActivity } from "@/hooks/use-activity";
import type { ActivityItem } from "@/types";

const ACTIVITY_ICONS: Record<string, React.ElementType> = {
  login: LogIn,
  password_change: KeyRound,
  profile_update: UserCog,
  address_added: MapPin,
  address_updated: MapPin,
  address_deleted: MapPin,
  order_placed: FileText,
  order_paid: FileText,
  order_shipped: FileText,
  order_delivered: FileText,
  preference_update: SlidersHorizontal,
  account_deletion_requested: Trash2,
};

function ActivityRow({ item }: { item: ActivityItem }) {
  const Icon = ACTIVITY_ICONS[item.type] || Activity;
  return (
    <div className="flex items-start gap-4 p-4 rounded-xl bg-surface-page border border-default">
      <div className="w-10 h-10 rounded-full bg-surface-card border border-default flex items-center justify-center text-secondary-600 flex-shrink-0">
        <Icon className="w-5 h-5" />
      </div>
      <div className="flex-1 min-w-0">
        <p className="font-semibold text-text-heading">{item.description}</p>
        <p className="text-sm text-muted">
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
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  const items = data?.data || [];
  const hasPages = data && data.last_page > 1;

  return (
    <>
      <PageHero
        title="Recent Activity"
        subtitle="Track recent changes and events on your account"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Activity" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Account
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                <Activity className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-text-heading">Recent Activity</h1>
                <p className="text-sm text-muted">A timeline of events related to your account.</p>
              </div>
            </div>

            {items.length === 0 ? (
              <div className="py-16 text-center text-muted">
                <Activity className="w-12 h-12 mx-auto mb-3 text-placeholder" />
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
              <div className="flex items-center justify-between mt-6 pt-6 border-t border-default">
                <button
                  onClick={() => setPage((p) => Math.max(1, p - 1))}
                  disabled={page <= 1}
                  className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-default text-sm font-medium text-body hover:bg-surface-page disabled:opacity-50"
                >
                  <PrevIcon className="w-4 h-4" />
                  Previous
                </button>
                <span className="text-sm text-muted">
                  Page {page} of {data.last_page}
                </span>
                <button
                  onClick={() => setPage((p) => Math.min(data.last_page, p + 1))}
                  disabled={page >= data.last_page}
                  className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-default text-sm font-medium text-body hover:bg-surface-page disabled:opacity-50"
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
