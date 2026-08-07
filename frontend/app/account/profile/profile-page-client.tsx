"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { Camera, ChevronLeft, Loader2, CheckCircle2, AlertCircle, User } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useProfile } from "@/hooks/use-profile";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { Customer } from "@/types";

const GENDER_OPTIONS: { value: NonNullable<Customer["gender"]>; label: string }[] = [
  { value: "male", label: "Male" },
  { value: "female", label: "Female" },
  { value: "other", label: "Other" },
  { value: "prefer_not_to_say", label: "Prefer not to say" },
];

export function ProfilePageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: profile, isLoading: profileLoading, update, isUpdating } = useProfile();

  const [form, setForm] = useState({
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    date_of_birth: "",
    gender: "",
  });
  const [error, setError] = useState("");

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  useEffect(() => {
    const source = profile || user;
    if (source) {
      setForm({
        first_name: source.first_name || "",
        last_name: source.last_name || "",
        email: source.email || "",
        phone: source.phone || "",
        date_of_birth: source.date_of_birth ? source.date_of_birth.split("T")[0] : "",
        gender: source.gender || "",
      });
    }
  }, [profile, user]);

  const handleChange = (field: keyof typeof form, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");

    try {
      await update({
        first_name: form.first_name || null,
        last_name: form.last_name || null,
        phone: form.phone || null,
        date_of_birth: form.date_of_birth || null,
        gender: (form.gender as Customer["gender"]) || null,
      });
      toastSuccess("Profile updated successfully.");
    } catch (err: unknown) {
      const message = err instanceof Error ? err.message : "Failed to update profile.";
      setError(message);
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

  if (!isAuthenticated) return null;

  const avatarUrl = profile?.avatar_url || user?.avatar_url;

  return (
    <>
      <PageHero
        title="Edit Profile"
        subtitle="Manage your personal contact information"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Profile" }]}
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

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8 mb-6">
            <div className="flex flex-col sm:flex-row sm:items-center gap-4">
              <div className="relative w-20 h-20 rounded-full overflow-hidden bg-secondary-50 border-2 border-surface-card shadow-md flex-shrink-0">
                {avatarUrl ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={avatarUrl}
                    alt={user?.name || "Profile"}
                    className="absolute inset-0 w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-secondary-600">
                    <User className="w-8 h-8" />
                  </div>
                )}
              </div>
              <div className="flex-1 min-w-0">
                <h2 className="text-lg font-bold text-text-heading">Profile Photo</h2>
                <p className="text-sm text-muted mt-1">
                  {avatarUrl
                    ? "Update or remove the photo shown on your account."
                    : "Add a photo so your account is easier to recognize."}
                </p>
              </div>
              <Link
                href="/account/profile/photo"
                className="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-secondary-600 rounded-xl hover:bg-secondary-700 transition-colors-base w-full sm:w-auto"
              >
                <Camera className="w-4 h-4" />
                {avatarUrl ? "Change Photo" : "Add Photo"}
              </Link>
            </div>
          </div>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 rounded-xl bg-secondary-50 text-secondary-600">
                <User className="w-5 h-5" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-text-heading">Profile Information</h1>
                <p className="text-sm text-muted">Manage your name, contact details, and preferences.</p>
              </div>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">First Name</label>
                  <input
                    type="text"
                    value={form.first_name}
                    onChange={(e) => handleChange("first_name", e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Last Name</label>
                  <input
                    type="text"
                    value={form.last_name}
                    onChange={(e) => handleChange("last_name", e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Email</label>
                  <input
                    type="email"
                    disabled
                    value={form.email}
                    className="w-full px-4 py-2.5 rounded-xl border border-default bg-surface-page text-placeholder cursor-not-allowed"
                  />
                  <p className="text-xs text-placeholder mt-1">
                    Email cannot be changed here. Contact support if you need to update it.
                  </p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Phone</label>
                  <input
                    type="tel"
                    value={form.phone}
                    onChange={(e) => handleChange("phone", e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
              </div>

              <div className="grid sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Date of Birth</label>
                  <input
                    type="date"
                    value={form.date_of_birth}
                    onChange={(e) => handleChange("date_of_birth", e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-text-heading mb-1">Gender</label>
                  <select
                    value={form.gender}
                    onChange={(e) => handleChange("gender", e.target.value)}
                    className="w-full px-4 py-2.5 rounded-xl border border-default focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 outline-none bg-surface-card"
                  >
                    <option value="">Select gender</option>
                    {GENDER_OPTIONS.map((opt) => (
                      <option key={opt.value} value={opt.value}>
                        {opt.label}
                      </option>
                    ))}
                  </select>
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
                Save Changes
              </button>
            </form>
          </div>
        </Container>
      </section>
    </>
  );
}
