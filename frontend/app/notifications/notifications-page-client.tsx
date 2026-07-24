"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Bell, CheckCheck, ChevronLeft, Loader2, Trash2, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useNotifications, useMarkNotificationAsRead, useMarkAllNotificationsAsRead, useDeleteNotification } from "@/hooks/use-notifications";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { Notification } from "@/lib/api/notifications";

function NotificationItem({ notification }: { notification: Notification }) {
  const markAsRead = useMarkNotificationAsRead();
  const deleteNotification = useDeleteNotification();
  const isUnread = !notification.read_at;

  const handleMarkAsRead = async () => {
    try {
      await markAsRead.mutateAsync(notification.id);
    } catch (err: unknown) {
      toastError(err instanceof Error ? err.message : "Failed to mark as read.");
    }
  };

  const handleDelete = async () => {
    try {
      await deleteNotification.mutateAsync(notification.id);
      toastSuccess("Notification deleted.");
    } catch (err: unknown) {
      toastError(err instanceof Error ? err.message : "Failed to delete notification.");
    }
  };

  return (
    <div
      className={`p-4 rounded-xl border transition-colors ${
        isUnread ? "bg-green-50/30 border-green-200" : "bg-white border-[#e2e8f0]"
      }`}
    >
      <div className="flex items-start gap-3">
        <div className={`w-2 h-2 mt-2 rounded-full ${isUnread ? "bg-green-600" : "bg-gray-300"}`} />
        <div className="flex-1 min-w-0">
          <p className={`font-semibold ${isUnread ? "text-[#0a1628]" : "text-[#64748b]"}`}>
            {notification.title || "Notification"}
          </p>
          {notification.message && (
            <div
              className="text-sm text-[#64748b] mt-1 prose prose-sm max-w-none"
              dangerouslySetInnerHTML={{ __html: notification.message }}
            />
          )}
          <p className="text-xs text-[#94a3b8] mt-2">
            {new Date(notification.created_at).toLocaleString()}
          </p>
        </div>
        <div className="flex items-center gap-2">
          {isUnread && (
            <button
              onClick={handleMarkAsRead}
              disabled={markAsRead.isPending}
              className="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
              title="Mark as read"
            >
              <CheckCheck className="w-4 h-4" />
            </button>
          )}
          <button
            onClick={handleDelete}
            disabled={deleteNotification.isPending}
            className="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
            title="Delete"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}

export function NotificationsPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const [page, setPage] = useState(1);
  const { data, isLoading, error } = useNotifications(page);
  const markAllAsRead = useMarkAllNotificationsAsRead();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleMarkAllAsRead = async () => {
    try {
      await markAllAsRead.mutateAsync();
      toastSuccess("All notifications marked as read.");
    } catch (err: unknown) {
      toastError(err instanceof Error ? err.message : "Failed to mark all as read.");
    }
  };

  if (authLoading || isLoading) {
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
        title="Notifications"
        subtitle="Stay updated on your orders, account, and announcements"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Notifications" }]}
      />

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <Link
              href="/account"
              className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628]"
            >
              <ChevronLeft className="w-4 h-4" />
              Back to Account
            </Link>

            <div className="flex items-center gap-3">
              <Link
                href="/notifications/preferences"
                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] rounded-xl hover:border-green-500 transition-colors"
              >
                <Bell className="w-4 h-4" />
                Preferences
              </Link>
              <button
                onClick={handleMarkAllAsRead}
                disabled={markAllAsRead.isPending || !data?.notifications.some((n) => !n.read_at)}
                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-green-700 bg-green-50 rounded-xl hover:bg-green-100 transition-colors disabled:opacity-50"
              >
                <CheckCheck className="w-4 h-4" />
                Mark all read
              </button>
            </div>
          </div>

          {error && (
            <div className="flex items-center gap-2 text-sm text-red-600 bg-red-50 p-4 rounded-xl mb-6">
              <AlertCircle className="w-4 h-4" />
              {error.message}
            </div>
          )}

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
            <h1 className="text-lg font-bold text-[#0a1628] mb-6">Your Notifications</h1>

            {!data || data.notifications.length === 0 ? (
              <div className="text-center py-12">
                <Bell className="w-12 h-12 mx-auto text-[#cbd5e1] mb-4" />
                <p className="text-[#64748b]">No notifications yet.</p>
              </div>
            ) : (
              <>
                <div className="space-y-3">
                  {data.notifications.map((notification) => (
                    <NotificationItem key={notification.id} notification={notification} />
                  ))}
                </div>

                {data.hasMore && (
                  <div className="mt-6 text-center">
                    <button
                      onClick={() => setPage((p) => p + 1)}
                      className="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-[#e2e8f0] text-[#0a1628] font-semibold rounded-xl hover:border-green-500 transition-colors"
                    >
                      Load more
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
