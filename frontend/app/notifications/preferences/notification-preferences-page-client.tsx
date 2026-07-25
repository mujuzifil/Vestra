"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, SlidersHorizontal, Mail, MessageSquare, Bell, CheckCircle2, AlertCircle, ShieldAlert } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useNotificationPreferences } from "@/hooks/use-notifications";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { CustomerPreferences, NotificationPreferences } from "@/types";

const defaultPreferences: CustomerPreferences = {
  notification_preferences: {
    email_notifications: true,
    sms_notifications: true,
    push_notifications: false,
    order_updates: true,
    marketing_emails: false,
    promotional_sms: false,
    newsletter: false,
  },
  account_preferences: {},
  system_alerts: true,
  emergency_alerts: true,
};

interface PreferenceItem {
  key: keyof NotificationPreferences;
  label: string;
  description: string;
  icon: React.ElementType;
}

const NOTIFICATION_ITEMS: PreferenceItem[] = [
  {
    key: "email_notifications",
    label: "Email Notifications",
    description: "Receive general notifications by email.",
    icon: Mail,
  },
  {
    key: "sms_notifications",
    label: "SMS Notifications",
    description: "Receive general notifications by SMS.",
    icon: MessageSquare,
  },
  {
    key: "push_notifications",
    label: "Push Notifications",
    description: "Receive push notifications in your browser (future feature).",
    icon: Bell,
  },
  {
    key: "order_updates",
    label: "Order Updates",
    description: "Get notified about order status changes.",
    icon: CheckCircle2,
  },
  {
    key: "marketing_emails",
    label: "Marketing Emails",
    description: "Receive occasional news and offers from VESTRA.",
    icon: Mail,
  },
  {
    key: "promotional_sms",
    label: "Promotional SMS",
    description: "Receive special deals and discounts by SMS.",
    icon: MessageSquare,
  },
  {
    key: "newsletter",
    label: "Newsletter",
    description: "Subscribe to the VESTRA newsletter.",
    icon: Bell,
  },
];

export function NotificationPreferencesPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: preferences, isLoading, update, isUpdating } = useNotificationPreferences();

  const [form, setForm] = useState<CustomerPreferences>(defaultPreferences);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    if (preferences) {
      setForm({
        ...defaultPreferences,
        ...preferences,
        notification_preferences: {
          ...defaultPreferences.notification_preferences,
          ...preferences.notification_preferences,
        },
      });
    }
  }, [preferences]);

  const toggleNotification = (key: keyof NotificationPreferences) => {
    setForm((prev) => ({
      ...prev,
      notification_preferences: {
        ...prev.notification_preferences,
        [key]: !prev.notification_preferences[key],
      },
    }));
  };

  const toggleAlert = (key: "system_alerts" | "emergency_alerts") => {
    setForm((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    try {
      await update({
        notification_preferences: form.notification_preferences,
        system_alerts: form.system_alerts,
        emergency_alerts: form.emergency_alerts,
      });
      toastSuccess("Notification preferences saved.");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to save preferences.";
      setError(message);
      toastError(message);
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
        title="Notification Preferences"
        subtitle="Choose how VESTRA communicates with you"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Notifications", href: "/notifications" }, { label: "Preferences" }]}
      />

      <section className="py-12 lg:py-20 bg-neutral-50">
        <Container>
          <Link
            href="/notifications"
            className="inline-flex items-center gap-2 text-sm font-semibold text-text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Notifications
          </Link>

          <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
            <div className="bg-surface-card rounded-[20px] border border-border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                  <SlidersHorizontal className="w-5 h-5" />
                </div>
                <div>
                  <h1 className="text-lg font-bold text-text-heading">Notification Channels</h1>
                  <p className="text-sm text-text-muted">Select the channels and topics you want to receive.</p>
                </div>
              </div>

              <div className="space-y-4">
                {NOTIFICATION_ITEMS.map(({ key, label, description, icon: Icon }) => (
                  <label
                    key={key}
                    className="flex items-start gap-4 p-4 rounded-xl border border-border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base cursor-pointer"
                  >
                    <div className="p-2 rounded-lg bg-neutral-50 text-primary-500">
                      <Icon className="w-4 h-4" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold text-text-heading">{label}</p>
                      <p className="text-sm text-text-muted">{description}</p>
                    </div>
                    <input
                      type="checkbox"
                      checked={!!form.notification_preferences[key]}
                      onChange={() => toggleNotification(key)}
                      className="w-5 h-5 rounded border-border-default text-secondary-600 focus:ring-secondary-500 mt-1"
                    />
                  </label>
                ))}
              </div>
            </div>

            <div className="bg-surface-card rounded-[20px] border border-border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="p-2 rounded-xl bg-danger-50 text-danger-600">
                  <ShieldAlert className="w-5 h-5" />
                </div>
                <div>
                  <h2 className="text-lg font-bold text-text-heading">Alert Preferences</h2>
                  <p className="text-sm text-text-muted">Critical alerts bypass marketing preferences.</p>
                </div>
              </div>

              <div className="space-y-4">
                <label className="flex items-start gap-4 p-4 rounded-xl border border-border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base cursor-pointer">
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-text-heading">System Alerts</p>
                    <p className="text-sm text-text-muted">Security, maintenance, and account alerts.</p>
                  </div>
                  <input
                    type="checkbox"
                    checked={form.system_alerts}
                    onChange={() => toggleAlert("system_alerts")}
                    className="w-5 h-5 rounded border-border-default text-secondary-600 focus:ring-secondary-500 mt-1"
                  />
                </label>

                <label className="flex items-start gap-4 p-4 rounded-xl border border-border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base cursor-pointer">
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-text-heading">Emergency Alerts</p>
                    <p className="text-sm text-text-muted">Urgent platform and service alerts.</p>
                  </div>
                  <input
                    type="checkbox"
                    checked={form.emergency_alerts}
                    onChange={() => toggleAlert("emergency_alerts")}
                    className="w-5 h-5 rounded border-border-default text-secondary-600 focus:ring-secondary-500 mt-1"
                  />
                </label>
              </div>
            </div>

            {error && (
              <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                <AlertCircle className="w-4 h-4" />
                {error}
              </div>
            )}

            <button
              type="submit"
              disabled={isUpdating}
              className="inline-flex items-center gap-2 px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base disabled:opacity-50"
            >
              {isUpdating ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
              Save Preferences
            </button>
          </form>
        </Container>
      </section>
    </>
  );
}
