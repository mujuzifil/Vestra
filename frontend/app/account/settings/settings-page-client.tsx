"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  User,
  Lock,
  ChevronLeft,
  Loader2,
  CheckCircle2,
  AlertCircle,
  Camera,
  Shield,
  SlidersHorizontal,
  Activity,
  Trash2,
  ChevronRight,
} from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

export function SettingsPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const [activeTab, setActiveTab] = useState<"profile" | "password">("profile");

  // Profile form
  const [profileForm, setProfileForm] = useState({ name: "", phone: "" });
  const [profileSubmitting, setProfileSubmitting] = useState(false);
  const [profileSuccess, setProfileSuccess] = useState(false);
  const [profileError, setProfileError] = useState("");

  // Password form
  const [passwordForm, setPasswordForm] = useState({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const [passwordSubmitting, setPasswordSubmitting] = useState(false);
  const [passwordSuccess, setPasswordSuccess] = useState(false);
  const [passwordError, setPasswordError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    if (user) {
      setProfileForm({ name: user.name, phone: user.phone || "" });
    }
  }, [user]);

  const handleProfileSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setProfileSubmitting(true);
    setProfileSuccess(false);
    setProfileError("");
    try {
      const { updateProfile } = await import("@/lib/api/auth");
      await updateProfile(profileForm);
      setProfileSuccess(true);
      setTimeout(() => setProfileSuccess(false), 3000);
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to update profile";
      setProfileError(message);
    } finally {
      setProfileSubmitting(false);
    }
  };

  const handlePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setPasswordSubmitting(true);
    setPasswordSuccess(false);
    setPasswordError("");

    if (passwordForm.password !== passwordForm.password_confirmation) {
      setPasswordError("Passwords do not match");
      setPasswordSubmitting(false);
      return;
    }
    if (passwordForm.password.length < 8) {
      setPasswordError("Password must be at least 8 characters");
      setPasswordSubmitting(false);
      return;
    }

    try {
      const { changePassword } = await import("@/lib/api/auth");
      await changePassword(passwordForm.current_password, passwordForm.password);
      setPasswordSuccess(true);
      setPasswordForm({ current_password: "", password: "", password_confirmation: "" });
      setTimeout(() => setPasswordSuccess(false), 3000);
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to change password";
      setPasswordError(message);
    } finally {
      setPasswordSubmitting(false);
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
        title="Account Settings"
        subtitle="Manage your profile and security"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Settings" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-primary-900 mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Account
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            {/* Tabs */}
            <div className="flex gap-2 mb-8 border-b border-default">
              <button
                onClick={() => setActiveTab("profile")}
                className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-colors-base ${
                  activeTab === "profile"
                    ? "border-secondary-600 text-secondary-600"
                    : "border-transparent text-muted hover:text-primary-900"
                }`}
              >
                <User className="w-4 h-4" />
                Profile
              </button>
              <button
                onClick={() => setActiveTab("password")}
                className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-colors-base ${
                  activeTab === "password"
                    ? "border-secondary-600 text-secondary-600"
                    : "border-transparent text-muted hover:text-primary-900"
                }`}
              >
                <Lock className="w-4 h-4" />
                Password
              </button>
            </div>

            {/* Profile Tab */}
            {activeTab === "profile" && (
              <form onSubmit={handleProfileSubmit} className="max-w-lg space-y-4">
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">Full Name</label>
                  <input
                    type="text"
                    required
                    value={profileForm.name}
                    onChange={(e) => setProfileForm({ ...profileForm, name: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">Phone</label>
                  <input
                    type="tel"
                    value={profileForm.phone}
                    onChange={(e) => setProfileForm({ ...profileForm, phone: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">Email</label>
                  <input
                    type="email"
                    disabled
                    value={user?.email || ""}
                    className="w-full px-4 py-2.5 rounded-xl border border-default bg-surface-page text-placeholder cursor-not-allowed"
                  />
                  <p className="text-xs text-placeholder mt-1">Email cannot be changed.</p>
                </div>

                {profileSuccess && (
                  <div className="flex items-center gap-2 text-sm text-success-600 bg-success-50 p-3 rounded-xl">
                    <CheckCircle2 className="w-4 h-4" />
                    Profile updated successfully.
                  </div>
                )}
                {profileError && (
                  <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                    <AlertCircle className="w-4 h-4" />
                    {profileError}
                  </div>
                )}

                <button
                  type="submit"
                  disabled={profileSubmitting}
                  className="px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base disabled:opacity-50"
                >
                  {profileSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : "Save Changes"}
                </button>
              </form>
            )}

            {/* Password Tab */}
            {activeTab === "password" && (
              <form onSubmit={handlePasswordSubmit} className="max-w-lg space-y-4">
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">Current Password</label>
                  <input
                    type="password"
                    required
                    value={passwordForm.current_password}
                    onChange={(e) => setPasswordForm({ ...passwordForm, current_password: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">New Password</label>
                  <input
                    type="password"
                    required
                    minLength={8}
                    value={passwordForm.password}
                    onChange={(e) => setPasswordForm({ ...passwordForm, password: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-primary-900 mb-1">Confirm New Password</label>
                  <input
                    type="password"
                    required
                    value={passwordForm.password_confirmation}
                    onChange={(e) => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>

                {passwordSuccess && (
                  <div className="flex items-center gap-2 text-sm text-success-600 bg-success-50 p-3 rounded-xl">
                    <CheckCircle2 className="w-4 h-4" />
                    Password changed successfully.
                  </div>
                )}
                {passwordError && (
                  <div className="flex items-center gap-2 text-sm text-danger-600 bg-danger-50 p-3 rounded-xl">
                    <AlertCircle className="w-4 h-4" />
                    {passwordError}
                  </div>
                )}

                <button
                  type="submit"
                  disabled={passwordSubmitting}
                  className="px-6 py-2.5 bg-secondary-600 text-white font-semibold rounded-xl hover:bg-secondary-600 transition-colors-base disabled:opacity-50"
                >
                  {passwordSubmitting ? <Loader2 className="w-4 h-4 animate-spin" /> : "Change Password"}
                </button>
              </form>
            )}

            {/* More Settings */}
            <div className="mt-10 pt-8 border-t border-default">
              <h2 className="text-base font-bold text-primary-900 mb-4">More Settings</h2>
              <div className="grid sm:grid-cols-2 gap-3">
                <Link
                  href="/account/profile/photo"
                  className="flex items-center gap-3 p-4 rounded-xl border border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base"
                >
                  <div className="p-2 rounded-lg bg-surface-page text-primary-500">
                    <Camera className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-primary-900">Profile Photo</p>
                    <p className="text-xs text-muted">Upload or remove avatar</p>
                  </div>
                  <ChevronRight className="w-4 h-4 text-placeholder" />
                </Link>
                <Link
                  href="/account/security"
                  className="flex items-center gap-3 p-4 rounded-xl border border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base"
                >
                  <div className="p-2 rounded-lg bg-surface-page text-primary-500">
                    <Shield className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-primary-900">Security</p>
                    <p className="text-xs text-muted">Sessions and login activity</p>
                  </div>
                  <ChevronRight className="w-4 h-4 text-placeholder" />
                </Link>
                <Link
                  href="/account/preferences"
                  className="flex items-center gap-3 p-4 rounded-xl border border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base"
                >
                  <div className="p-2 rounded-lg bg-surface-page text-primary-500">
                    <SlidersHorizontal className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-primary-900">Preferences</p>
                    <p className="text-xs text-muted">Notifications and account prefs</p>
                  </div>
                  <ChevronRight className="w-4 h-4 text-placeholder" />
                </Link>
                <Link
                  href="/account/activity"
                  className="flex items-center gap-3 p-4 rounded-xl border border-default hover:border-secondary-200 hover:bg-secondary-50/30 transition-colors-base"
                >
                  <div className="p-2 rounded-lg bg-surface-page text-primary-500">
                    <Activity className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-primary-900">Activity</p>
                    <p className="text-xs text-muted">Account event timeline</p>
                  </div>
                  <ChevronRight className="w-4 h-4 text-placeholder" />
                </Link>
                <Link
                  href="/account/delete"
                  className="flex items-center gap-3 p-4 rounded-xl border border-danger-100 hover:bg-danger-50 transition-colors-base sm:col-span-2"
                >
                  <div className="p-2 rounded-lg bg-danger-50 text-danger-600">
                    <Trash2 className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-danger-600">Delete Account</p>
                    <p className="text-xs text-danger-400">Request permanent account deletion</p>
                  </div>
                  <ChevronRight className="w-4 h-4 text-danger-400" />
                </Link>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
