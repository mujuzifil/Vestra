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
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
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

      <section className="py-12 lg:py-20 bg-[#f8fafc]">
        <Container>
          <Link
            href="/account/settings"
            className="inline-flex items-center gap-2 text-sm font-semibold text-[#64748b] hover:text-[#0a1628] mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Settings
          </Link>

          <div className="grid lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 space-y-6">
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
                <div className="flex items-center gap-3 mb-6">
                  <div className="p-2 rounded-xl bg-green-50 text-green-600">
                    <Shield className="w-5 h-5" />
                  </div>
                  <div>
                    <h1 className="text-lg font-bold text-[#0a1628]">Security Overview</h1>
                    <p className="text-sm text-[#64748b]">Monitor recent activity and signed-in devices.</p>
                  </div>
                </div>

                <div className="space-y-4">
                  <div className="p-4 rounded-xl bg-[#f8fafc] border border-[#e2e8f0] flex items-start gap-4">
                    <Clock className="w-5 h-5 text-green-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-[#0a1628]">Last Login</p>
                      <p className="text-sm text-[#64748b]">
                        {user.updated_at ? new Date(user.updated_at).toLocaleString() : "Unknown"}
                      </p>
                    </div>
                  </div>

                  <div className="p-4 rounded-xl bg-[#f8fafc] border border-[#e2e8f0] flex items-start gap-4">
                    <Shield className="w-5 h-5 text-green-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-[#0a1628]">Password</p>
                      <p className="text-sm text-[#64748b]">Last changed on {new Date(user.updated_at).toLocaleDateString()}</p>
                    </div>
                  </div>

                  <div className="p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-4">
                    <AlertCircle className="w-5 h-5 text-amber-600 mt-0.5" />
                    <div>
                      <p className="font-semibold text-amber-800">Two-Factor Authentication</p>
                      <p className="text-sm text-amber-700">
                        2FA is not yet configurable from this screen. Contact support to enable it.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8">
                <h2 className="text-lg font-bold text-[#0a1628] mb-4">Active Sessions</h2>
                <div className="space-y-3">
                  {mockSessions.map((session) => (
                    <div
                      key={session.id}
                      className="flex items-start sm:items-center justify-between gap-4 p-4 rounded-xl bg-[#f8fafc] border border-[#e2e8f0]"
                    >
                      <div className="flex items-start gap-3">
                        <div className="p-2 rounded-lg bg-white text-[#0d3b66]">
                          <Smartphone className="w-4 h-4" />
                        </div>
                        <div>
                          <p className="font-semibold text-[#0a1628]">
                            {session.device}
                            {session.isCurrent && (
                              <span className="ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">
                                Current
                              </span>
                            )}
                          </p>
                          <p className="text-sm text-[#64748b]">
                            <Globe className="w-3 h-3 inline mr-1" />
                            {session.location} · {session.ip}
                          </p>
                          <p className="text-xs text-[#94a3b8]">Last active {session.lastActive}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            <div className="lg:col-span-1">
              <div className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 sticky top-24">
                <h2 className="text-base font-bold text-[#0a1628] mb-4">Quick Links</h2>
                <nav className="space-y-2">
                  <Link
                    href="/account/password"
                    className="block p-3 rounded-xl text-sm font-medium text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628]"
                  >
                    Change Password
                  </Link>
                  <Link
                    href="/account/preferences"
                    className="block p-3 rounded-xl text-sm font-medium text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628]"
                  >
                    Notification Preferences
                  </Link>
                  <Link
                    href="/account/activity"
                    className="block p-3 rounded-xl text-sm font-medium text-[#475569] hover:bg-[#f8fafc] hover:text-[#0a1628]"
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
