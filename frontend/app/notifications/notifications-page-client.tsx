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
      className={`p-4 rounded-xl border transition-colors-base ${
        isUnread ? "bg-secondary-50/30 border-secondary-200" : "bg-surface-card border-border-default"
      }`}
    >
      <div className="flex items-start gap-3">
        <div className={`w-2 h-2 mt-2 rounded-full ${isUnread ? "bg-secondary-600" : "bg-neutral-300"}`} />
        <div className="flex-1 min-w-0">
          <p className={`font-semibold ${isUnread ? "text-text-heading" : "text-text-muted"}`}>
            {notification.title || "Notification"}
          </p>
          {notification.message && (
            <div
              className="text-sm text-text-muted mt-1 prose prose-sm max-w-none"
              dangerouslySetInnerHTML={{ __html: notification.message }}
            />
          )}
          <p className="text-xs text-text-placeholder mt-2">
            {new Date(notification.created_at).toLocaleString()}
          </p>
        </div>
        <div className="flex items-center gap-2">
          {isUnread && (
            <button
              onClick={handleMarkAsRead}
              disabled={markAsRead.isPending}
              className="p-2 text-secondary-600 hover:bg-secondary-100 rounded-lg transition-colors-base"
              title="Mark as read"
            >
              <CheckCheck className="w-4 h-4" />
            </button>
          )}
          <button
            onClick={handleDelete}
            disabled={deleteNotification.isPending}
            className="p-2 text-danger-600 hover:bg-danger-100 rounded-lg transition-colors-base"
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
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
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

      <section className="py-12 lg:py-20 bg-neutral-50">
        <Container>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <Link
              href="/account"
              className="inline-flex items-center gap-2 text-sm font-semibold text-text-muted hover:text-text-heading"
            >
              <ChevronLeft className="w-4 h-4" />
              Back to Account
            </Link>

            <div className="flex items-center gap-3">
              <Link
                href="/notifications/preferences"
                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-text-heading bg-surface-card border border-border-default rounded-xl hover:border-secondary-500 transition-colors-base"
              >
                <Bell className="w-4 h-4" />
                Preferences
              </Link>
              <button
                onClick={handleMarkAllAsRead}
                disabled={markAllAsRead.isPending || !data?.notifications?.some((n) => !n.read_at)}
                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-secondary-600 bg-secondary-50 rounded-xl hover:bg-secondary-100 transition-colors-base disabled:opacity-50"
              >
                <CheckCheck className="w-4 h-4" />
                Mark all read
              </button>
            </div>
          </div>

          {error && (
            <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-4 rounded-xl mb-6">
              <AlertCircle className="w-4 h-4" />
              {error.message}
            </div>
          )}

          <div className="bg-surface-card rounded-[20px] border border-border-default shadow-sm p-6 lg:p-8">
            <h1 className="text-lg font-bold text-text-heading mb-6">Your Notifications</h1>

            {!data || data.notifications.length === 0 ? (
              <div className="text-center py-12">
                <Bell className="w-12 h-12 mx-auto text-neutral-300 mb-4" />
                <p className="text-text-muted">No notifications yet.</p>
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
                      className="inline-flex items-center gap-2 px-6 py-2.5 bg-surface-card border border-border-default text-text-heading font-semibold rounded-xl hover:border-secondary-500 transition-colors-base"
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
