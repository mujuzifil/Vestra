"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, Trash2, AlertTriangle, AlertCircle, CheckCircle2, Eye, EyeOff } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useDeleteRequest } from "@/hooks/use-delete-request";
import { toastError, toastSuccess } from "@/lib/toast-utils";

export function DeletePageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading, logout } = useAuth();
  const { submit, isSubmitting } = useDeleteRequest();

  const [step, setStep] = useState<"confirm" | "reason">("confirm");
  const [reason, setReason] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [confirmed, setConfirmed] = useState(false);
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleFirstConfirm = (e: React.FormEvent) => {
    e.preventDefault();
    if (!confirmed) {
      setError("Please confirm that you understand this action.");
      return;
    }
    setError("");
    setStep("reason");
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    if (!password) {
      setError("Please enter your password to continue.");
      return;
    }

    try {
      await submit({ reason: reason || undefined, password });
      toastSuccess("Account deletion request submitted. You will be signed out.");
      await logout();
      router.push("/");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to submit deletion request.";
      setError(message);
      toastError(message);
    }
  };

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) return null;

  return (
    <>
      <PageHero
        title="Delete Account"
        subtitle="Request permanent deletion of your account"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Delete" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account/settings"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Settings
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8 max-w-2xl">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-danger-50 text-danger-600">
                <Trash2 className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-text-heading">Delete Account</h1>
                <p className="text-sm text-muted">This action cannot be undone.</p>
              </div>
            </div>

            {step === "confirm" && (
              <form onSubmit={handleFirstConfirm} className="space-y-5">
                <div className="p-4 rounded-xl bg-danger-50 border border-danger-100 text-danger-600 text-sm space-y-2">
                  <div className="flex items-start gap-2">
                    <AlertTriangle className="w-4 h-4 mt-0.5 flex-shrink-0" />
                    <p className="font-semibold">Warning</p>
                  </div>
                  <p>Requesting account deletion will start a review process. Once completed:</p>
                  <ul className="list-disc list-inside space-y-1 ml-1">
                    <li>Your personal data will be anonymized or removed.</li>
                    <li>You will lose access to order history and saved addresses.</li>
                    <li>This action cannot be reversed.</li>
                  </ul>
                </div>

                <label className="flex items-start gap-3 text-sm text-body">
                  <input
                    type="checkbox"
                    checked={confirmed}
                    onChange={(e) => setConfirmed(e.target.checked)}
                    className="w-4 h-4 rounded border-default text-secondary-600 focus:ring-secondary-500 mt-0.5"
                  />
                  I understand that deleting my account is permanent and will remove my data.
                </label>

                {error && (
                  <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                    <AlertCircle className="w-4 h-4" />
                    {error}
                  </div>
                )}

                <button
                  type="submit"
                  className="inline-flex items-center gap-2 px-6 py-2.5 bg-danger-600 text-white font-semibold rounded-xl hover:bg-danger-600 transition-colors-base"
                >
                  Continue
                  <ChevronLeft className="w-4 h-4 rotate-180" />
                </button>
              </form>
            )}

            {step === "reason" && (
              <form onSubmit={handleSubmit} className="space-y-5">
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">
                    Reason for leaving <span className="text-placeholder">(optional)</span>
                  </label>
                  <textarea
                    rows={3}
                    value={reason}
                    onChange={(e) => setReason(e.target.value)}
                    placeholder="Tell us why you're leaving..."
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none resize-none"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Password</label>
                  <div className="relative">
                    <input
                      type={showPassword ? "text" : "password"}
                      required
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      className="w-full px-4 py-2.5 pr-10 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword((s) => !s)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-placeholder hover:text-muted"
                    >
                      {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                  <p className="text-xs text-placeholder mt-1">Enter your password to confirm this request.</p>
                </div>

                {error && (
                  <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                    <AlertCircle className="w-4 h-4" />
                    {error}
                  </div>
                )}

                <div className="flex items-center gap-3">
                  <button
                    type="button"
                    onClick={() => setStep("confirm")}
                    className="px-6 py-2.5 border border-default text-muted font-semibold rounded-xl hover:bg-surface-page transition-colors-base"
                  >
                    Back
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="inline-flex items-center gap-2 px-6 py-2.5 bg-danger-600 text-white font-semibold rounded-xl hover:bg-danger-600 transition-colors-base disabled:opacity-50"
                  >
                    {isSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
                    Submit Deletion Request
                  </button>
                </div>
              </form>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
