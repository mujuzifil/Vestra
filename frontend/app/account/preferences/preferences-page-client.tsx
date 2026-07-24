"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, SlidersHorizontal, Mail, MessageSquare, Bell, CheckCircle2, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { usePreferences } from "@/hooks/use-preferences";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { CustomerPreferences } from "@/types";

const PREFERENCE_GROUPS: {
  key: keyof CustomerPreferences;
  label: string;
  description: string;
  icon: React.ElementType;
}[] = [
  {
    key: "email_marketing",
    label: "Marketing Emails",
    description: "Receive occasional news and offers from VESTRA.",
    icon: Mail,
  },
  {
    key: "order_updates_email",
    label: "Order Updates by Email",
    description: "Get email notifications about your orders.",
    icon: Mail,
  },
  {
    key: "order_updates_sms",
    label: "Order Updates by SMS",
    description: "Get SMS notifications about your orders.",
    icon: MessageSquare,
  },
  {
    key: "promotional_emails",
    label: "Promotional Emails",
    description: "Receive special deals and discounts.",
    icon: Bell,
  },
  {
    key: "login_alerts",
    label: "Login Alerts",
    description: "Be notified when a new device signs in to your account.",
    icon: Bell,
  },
];

const defaultPreferences: CustomerPreferences = {
  email_marketing: false,
  sms_notifications: false,
  order_updates_email: true,
  order_updates_sms: false,
  promotional_emails: false,
  two_factor_enabled: false,
  login_alerts: true,
  profile_visibility: "private",
  language: "en",
  currency: "UGX",
};

export function PreferencesPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: preferences, isLoading, update, isUpdating } = usePreferences();

  const [form, setForm] = useState<CustomerPreferences>(defaultPreferences);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    if (preferences) {
      setForm({ ...defaultPreferences, ...preferences });
    }
  }, [preferences]);

  const toggle = (key: keyof CustomerPreferences) => {
    setForm((prev) => ({ ...prev, [key]: !prev[key] }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    try {
      await update(form);
      toastSuccess("Preferences saved.");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to save preferences.";
      setError(message);
      toastError(message);
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
        title="Preferences"
        subtitle="Manage notifications and account preferences"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Preferences" }]}
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

          <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 max-w-2xl">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-green-50 text-green-600">
                <SlidersHorizontal className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-[#0a1628]">Account Preferences</h1>
                <p className="text-sm text-[#64748b]">Choose how VESTRA communicates with you.</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              {PREFERENCE_GROUPS.map(({ key, label, description, icon: Icon }) => (
                <label
                  key={key}
                  className="flex items-start gap-4 p-4 rounded-xl border border-[#e2e8f0] hover:border-green-200 hover:bg-green-50/30 transition-colors cursor-pointer"
                >
                  <div className="p-2 rounded-lg bg-[#f8fafc] text-[#0d3b66]">
                    <Icon className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-[#0a1628]">{label}</p>
                    <p className="text-sm text-[#64748b]">{description}</p>
                  </div>
                  <input
                    type="checkbox"
                    checked={!!form[key]}
                    onChange={() => toggle(key)}
                    className="w-5 h-5 rounded border-[#e2e8f0] text-green-600 focus:ring-green-500 mt-1"
                  />
                </label>
              ))}

              <div className="grid sm:grid-cols-2 gap-4 pt-2">
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Language</label>
                  <select
                    value={form.language}
                    onChange={(e) => setForm((prev) => ({ ...prev, language: e.target.value }))}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none bg-white"
                  >
                    <option value="en">English</option>
                    <option value="sw">Swahili</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-[#0a1628] mb-1">Currency</label>
                  <select
                    value={form.currency}
                    onChange={(e) => setForm((prev) => ({ ...prev, currency: e.target.value }))}
                    className="w-full px-4 py-2.5 rounded-xl border border-[#e2e8f0] focus:border-green-500 focus:ring-1 focus:ring-green-500 outline-none bg-white"
                  >
                    <option value="UGX">Ugandan Shilling (UGX)</option>
                    <option value="USD">US Dollar (USD)</option>
                  </select>
                </div>
              </div>

              {error && (
                <div className="flex items-center gap-2 text-sm text-red-600 bg-red-50 p-3 rounded-xl">
                  <AlertCircle className="w-4 h-4" />
                  {error}
                </div>
              )}

              <button
                type="submit"
                disabled={isUpdating}
                className="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors disabled:opacity-50"
              >
                {isUpdating ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
                Save Preferences
              </button>
            </form>
          </div>
        </Container>
      </section>
    </>
  );
}
