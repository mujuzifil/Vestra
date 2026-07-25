"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, Lock, CheckCircle2, AlertCircle, Eye, EyeOff } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { changePassword } from "@/lib/api/auth";
import { toastError, toastSuccess } from "@/lib/toast-utils";

export function PasswordPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();

  const [form, setForm] = useState({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const [show, setShow] = useState({ current: false, new: false, confirm: false });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess(false);

    if (form.password !== form.password_confirmation) {
      setError("Passwords do not match.");
      return;
    }
    if (form.password.length < 8) {
      setError("Password must be at least 8 characters.");
      return;
    }

    setIsSubmitting(true);
    try {
      await changePassword(form.current_password, form.password);
      setForm({ current_password: "", password: "", password_confirmation: "" });
      setSuccess(true);
      toastSuccess("Password changed successfully.");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to change password.";
      setError(message);
      toastError(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  if (authLoading) {
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
        title="Change Password"
        subtitle="Keep your account secure with a strong password"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Password" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account/settings"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-primary-900 mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Settings
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8 max-w-2xl">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                <Lock className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-primary-900">Change Password</h1>
                <p className="text-sm text-muted">Update the password you use to sign in.</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              <div>
                <label className="block text-sm font-medium text-primary-900 mb-1">Current Password</label>
                <div className="relative">
                  <input
                    type={show.current ? "text" : "password"}
                    required
                    value={form.current_password}
                    onChange={(e) => setForm({ ...form, current_password: e.target.value })}
                    className="w-full px-4 py-2.5 pr-10 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                  <button
                    type="button"
                    onClick={() => setShow((s) => ({ ...s, current: !s.current }))}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-placeholder hover:text-muted"
                  >
                    {show.current ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-primary-900 mb-1">New Password</label>
                <div className="relative">
                  <input
                    type={show.new ? "text" : "password"}
                    required
                    minLength={8}
                    value={form.password}
                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                    className="w-full px-4 py-2.5 pr-10 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                  <button
                    type="button"
                    onClick={() => setShow((s) => ({ ...s, new: !s.new }))}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-placeholder hover:text-muted"
                  >
                    {show.new ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-primary-900 mb-1">Confirm New Password</label>
                <div className="relative">
                  <input
                    type={show.confirm ? "text" : "password"}
                    required
                    value={form.password_confirmation}
                    onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                    className="w-full px-4 py-2.5 pr-10 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                  <button
                    type="button"
                    onClick={() => setShow((s) => ({ ...s, confirm: !s.confirm }))}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-placeholder hover:text-muted"
                  >
                    {show.confirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              {success && (
                <div className="flex items-center gap-2 text-sm text-success-600 bg-success-50 p-3 rounded-xl">
                  <CheckCircle2 className="w-4 h-4" />
                  Password changed successfully.
                </div>
              )}
              {error && (
                <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                  <AlertCircle className="w-4 h-4" />
                  {error}
                </div>
              )}

              <button
                type="submit"
                disabled={isSubmitting}
                className="inline-flex items-center gap-2 px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base disabled:opacity-50"
              >
                {isSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Lock className="w-4 h-4" />}
                Change Password
              </button>
            </form>
          </div>
        </Container>
      </section>
    </>
  );
}
