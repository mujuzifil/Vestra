"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, Shield, Clock, Lock, CheckCircle2, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useChangePassword } from "@/hooks/use-auth-mutations";
import { useProfile } from "@/hooks/use-profile";
import { toastError, toastSuccess } from "@/lib/toast-utils";

function formatDateTime(value: string | null | undefined): string {
  if (!value) return "Not available";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Not available";
  return date.toLocaleString("en-UG", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function passwordStrength(password: string): { score: number; label: string; hints: string[] } {
  const hints: string[] = [];
  if (password.length < 12) hints.push("At least 12 characters");
  if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) hints.push("Upper and lower case letters");
  if (!/[0-9]/.test(password)) hints.push("At least one number");
  if (!/[^A-Za-z0-9]/.test(password)) hints.push("At least one symbol");

  const score = 4 - hints.length;
  const labels = ["Too weak", "Weak", "Fair", "Good", "Strong"];
  return { score: Math.max(0, score), label: labels[Math.max(0, score)], hints };
}

export function SecurityPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: profile, isLoading: profileLoading, refetch } = useProfile();
  const changePassword = useChangePassword();

  const [currentPassword, setCurrentPassword] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [formError, setFormError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const source = profile || user;
  const strength = useMemo(() => passwordStrength(password), [password]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError("");

    if (password !== passwordConfirmation) {
      setFormError("New password and confirmation do not match.");
      return;
    }

    if (strength.hints.length > 0) {
      setFormError(`Password requirements: ${strength.hints.join("; ")}.`);
      return;
    }

    try {
      await changePassword.mutateAsync({ currentPassword, password });
      setCurrentPassword("");
      setPassword("");
      setPasswordConfirmation("");
      toastSuccess("Password updated successfully.");
      await refetch();
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to change password.";
      setFormError(message);
      toastError(message);
    }
  };

  if (authLoading || profileLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !source) return null;

  return (
    <>
      <PageHero
        title="Security"
        subtitle="Manage your password and review account security details"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Security" }]}
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

          <div className="space-y-6">
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                  <Shield className="w-5 h-5" />
                </div>
                <div>
                  <h1 className="text-lg font-bold text-text-heading">Security Overview</h1>
                  <p className="text-sm text-muted">Recent sign-in and password change timestamps from your account.</p>
                </div>
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div className="p-4 rounded-xl bg-surface-page border border-default flex items-start gap-4">
                  <Clock className="w-5 h-5 text-secondary-600 mt-0.5" />
                  <div>
                    <p className="font-semibold text-text-heading">Last Login</p>
                    <p className="text-sm text-muted">{formatDateTime(source.last_login_at)}</p>
                  </div>
                </div>

                <div className="p-4 rounded-xl bg-surface-page border border-default flex items-start gap-4">
                  <Lock className="w-5 h-5 text-secondary-600 mt-0.5" />
                  <div>
                    <p className="font-semibold text-text-heading">Last Password Change</p>
                    <p className="text-sm text-muted">{formatDateTime(source.password_changed_at)}</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                  <Lock className="w-5 h-5" />
                </div>
                <div>
                  <h2 className="text-lg font-bold text-text-heading">Password Management</h2>
                  <p className="text-sm text-muted">
                    Use a strong password with at least 12 characters, mixed case, a number, and a symbol.
                  </p>
                </div>
              </div>

              <form onSubmit={handleSubmit} className="grid lg:grid-cols-2 gap-5">
                <div className="lg:col-span-2">
                  <label className="block text-sm font-medium text-text-heading mb-1">Current Password</label>
                  <input
                    type="password"
                    required
                    autoComplete="current-password"
                    value={currentPassword}
                    onChange={(e) => setCurrentPassword(e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">New Password</label>
                  <input
                    type="password"
                    required
                    autoComplete="new-password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                  {password.length > 0 && (
                    <p className="text-xs text-muted mt-2">
                      Strength: <span className="font-semibold text-text-heading">{strength.label}</span>
                      {strength.hints.length > 0 ? ` — ${strength.hints.join(", ")}` : ""}
                    </p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Confirm Password</label>
                  <input
                    type="password"
                    required
                    autoComplete="new-password"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>

                {formError && (
                  <div className="lg:col-span-2 flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                    <AlertCircle className="w-4 h-4 flex-shrink-0" />
                    {formError}
                  </div>
                )}

                <div className="lg:col-span-2">
                  <button
                    type="submit"
                    disabled={changePassword.isPending}
                    className="inline-flex items-center gap-2 px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90 disabled:opacity-50"
                  >
                    {changePassword.isPending ? (
                      <Loader2 className="w-4 h-4 animate-spin" />
                    ) : (
                      <CheckCircle2 className="w-4 h-4" />
                    )}
                    Update Password
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
