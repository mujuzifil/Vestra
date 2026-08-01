"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, Loader2, Shield, Smartphone, Globe, Clock, AlertCircle } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

// Placeholder security data until backend exposes dedicated endpoints.
const mockSessions = [
  {
    id: "current",
    device: "Current browser",
    location: "Unknown location",
    ip: "127.0.0.1",
    lastActive: "Just now",
    isCurrent: true,
  },
];

export function SecurityPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

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
        title="Security"
        subtitle="Review your account security status"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings", href: "/account/settings" }, { label: "Security" }]}
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

          <div className="grid lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 space-y-6">
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
                <div className="flex items-center gap-3 mb-6">
                  <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                    <Shield className="w-5 h-5" />
                  </div>
                  <div>
                    <h1 className="text-lg font-bold text-text-heading">Security Overview</h1>
                    <p className="text-sm text-muted">Monitor recent activity and signed-in devices.</p>
                  </div>
                </div>

                <div className="space-y-4">
                  <div className="p-4 rounded-xl bg-surface-page border border-default flex items-start gap-4">
                    <Clock className="w-5 h-5 text-secondary-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-text-heading">Last Login</p>
                      <p className="text-sm text-muted">
                        {user.updated_at ? new Date(user.updated_at).toLocaleString() : "Unknown"}
                      </p>
                    </div>
                  </div>

                  <div className="p-4 rounded-xl bg-surface-page border border-default flex items-start gap-4">
                    <Shield className="w-5 h-5 text-secondary-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-text-heading">Password</p>
                      <p className="text-sm text-muted">Last changed on {new Date(user.updated_at).toLocaleDateString()}</p>
                    </div>
                  </div>

                  <div className="p-4 rounded-xl bg-warning-50 border border-warning-100 flex items-start gap-4">
                    <AlertCircle className="w-5 h-5 text-warning-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-warning-600">Two-Factor Authentication</p>
                      <p className="text-sm text-warning-600">
                        2FA is not yet configurable from this screen. Contact support to enable it.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
                <h2 className="text-lg font-bold text-text-heading mb-4">Active Sessions</h2>
                <div className="space-y-3">
                  {mockSessions.map((session) => (
                    <div
                      key={session.id}
                      className="flex items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-surface-page border border-default"
                    >
                      <div className="flex items-start gap-3">
                        <div className="p-2 rounded-lg bg-surface-card text-primary-500">
                          <Smartphone className="w-4 h-4" />
                        </div>
                        <div>
                          <p className="font-semibold text-text-heading">
                            {session.device}
                            {session.isCurrent && (
                              <span className="ml-2 px-2 py-0.5 bg-secondary-100 text-secondary-600 text-xs rounded-full">
                                Current
                              </span>
                            )}
                          </p>
                          <p className="text-sm text-muted">
                            <Globe className="w-3 h-3 inline mr-1" />
                            {session.location} · {session.ip}
                          </p>
                          <p className="text-xs text-placeholder">Last active {session.lastActive}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            <div className="lg:col-span-1">
              <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 sticky top-24">
                <h2 className="text-base font-bold text-text-heading mb-4">Quick Links</h2>
                <nav className="space-y-2">
                  <Link
                    href="/account/password"
                    className="block p-3 rounded-xl text-sm font-medium text-body hover:bg-surface-page hover:text-text-heading"
                  >
                    Change Password
                  </Link>
                  <Link
                    href="/account/preferences"
                    className="block p-3 rounded-xl text-sm font-medium text-body hover:bg-surface-page hover:text-text-heading"
                  >
                    Notification Preferences
                  </Link>
                  <Link
                    href="/account/activity"
                    className="block p-3 rounded-xl text-sm font-medium text-body hover:bg-surface-page hover:text-text-heading"
                  >
                    Account Activity
                  </Link>
                </nav>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
