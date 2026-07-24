"use client";

import { useState, useRef, useEffect } from "react";
import Link from "next/link";
import { Bell, CheckCheck, Loader2 } from "lucide-react";
import { useUnreadNotifications, useMarkNotificationAsRead, useMarkAllNotificationsAsRead } from "@/hooks/use-notifications";
import { toastError, toastSuccess } from "@/lib/toast-utils";

export function NotificationBell() {
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const { data, isLoading } = useUnreadNotifications();
  const markAsRead = useMarkNotificationAsRead();
  const markAllAsRead = useMarkAllNotificationsAsRead();
  const unreadCount = data?.total ?? 0;

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleMarkAsRead = async (id: string) => {
    try {
      await markAsRead.mutateAsync(id);
    } catch (err: unknown) {
      toastError(err instanceof Error ? err.message : "Failed to mark as read.");
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await markAllAsRead.mutateAsync();
      toastSuccess("All notifications marked as read.");
    } catch (err: unknown) {
      toastError(err instanceof Error ? err.message : "Failed to mark all as read.");
    }
  };

  return (
    <div className="relative" ref={containerRef}>
      <button
        onClick={() => setOpen(!open)}
        aria-label={`Notifications (${unreadCount} unread)`}
        className="relative text-white hover:text-green-400 transition-colors p-2 rounded-full focus-visible:ring-2 focus-visible:ring-green-500"
      >
        <Bell className="w-5 h-5" aria-hidden="true" />
        {unreadCount > 0 && (
          <span className="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
            {unreadCount > 99 ? "99+" : unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div className="absolute top-full right-0 pt-2 z-50 w-80 sm:w-96">
          <div className="bg-[#0a1628] border border-white/10 rounded-lg shadow-xl overflow-hidden">
            <div className="flex items-center justify-between px-4 py-3 border-b border-white/10">
              <h3 className="text-white font-semibold text-sm">Notifications</h3>
              {unreadCount > 0 && (
                <button
                  onClick={handleMarkAllAsRead}
                  disabled={markAllAsRead.isPending}
                  className="text-xs text-green-400 hover:text-green-300 flex items-center gap-1 disabled:opacity-50"
                >
                  {markAllAsRead.isPending ? <Loader2 className="w-3 h-3 animate-spin" /> : <CheckCheck className="w-3 h-3" />}
                  Mark all read
                </button>
              )}
            </div>

            <div className="max-h-[360px] overflow-y-auto">
              {isLoading ? (
                <div className="flex items-center justify-center py-8">
                  <Loader2 className="w-5 h-5 animate-spin text-green-500" />
                </div>
              ) : !data || data.notifications.length === 0 ? (
                <div className="text-center py-8 text-white/60 text-sm">
                  No new notifications
                </div>
              ) : (
                <ul className="divide-y divide-white/10">
                  {data.notifications.slice(0, 5).map((notification) => (
                    <li key={notification.id} className="px-4 py-3 hover:bg-white/5 transition-colors">
                      <div className="flex items-start gap-3">
                        <div className="w-2 h-2 mt-1.5 rounded-full bg-green-500 flex-shrink-0" />
                        <div className="flex-1 min-w-0">
                          <p className="text-sm text-white font-medium truncate">
                            {notification.title || "Notification"}
                          </p>
                          {notification.message && (
                            <p
                              className="text-xs text-white/70 mt-0.5 line-clamp-2"
                              dangerouslySetInnerHTML={{ __html: notification.message }}
                            />
                          )}
                          <p className="text-[10px] text-white/50 mt-1">
                            {new Date(notification.created_at).toLocaleDateString()}
                          </p>
                        </div>
                        <button
                          onClick={() => handleMarkAsRead(notification.id)}
                          disabled={markAsRead.isPending}
                          className="text-xs text-green-400 hover:text-green-300 flex-shrink-0"
                        >
                          Read
                        </button>
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </div>

            <div className="border-t border-white/10 px-4 py-2">
              <Link
                href="/notifications"
                onClick={() => setOpen(false)}
                className="block text-center text-xs text-green-400 hover:text-green-300 py-1"
              >
                View all notifications
              </Link>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
