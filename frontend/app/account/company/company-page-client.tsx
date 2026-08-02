"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { User, Mail, Phone, Building2, Loader2, ArrowRight, Save } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useCompanyProfile, useUpdateCompanyProfile } from "@/hooks/use-company-profile";
import { Button } from "@/components/ui/button";
import type { CompanyProfile } from "@/types";

interface FormState {
  company_name: string;
  industry: string;
  business_type: string;
  tax_identification: string;
  registration_number: string;
  website: string;
  district: string;
  city: string;
  country: string;
  address: string;
  primary_contact_name: string;
  primary_contact_phone: string;
  primary_contact_email: string;
}

function toFormState(profile?: CompanyProfile | null): FormState {
  return {
    company_name: profile?.company_name ?? "",
    industry: profile?.industry ?? "",
    business_type: profile?.business_type ?? "",
    tax_identification: profile?.tax_identification ?? "",
    registration_number: profile?.registration_number ?? "",
    website: profile?.website ?? "",
    district: profile?.district ?? "",
    city: profile?.city ?? "",
    country: profile?.country ?? "",
    address: profile?.address ?? "",
    primary_contact_name: profile?.primary_contact_name ?? "",
    primary_contact_phone: profile?.primary_contact_phone ?? "",
    primary_contact_email: profile?.primary_contact_email ?? "",
  };
}

export function CompanyPageClient() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const { data: profile, isLoading: profileLoading } = useCompanyProfile();
  const updateProfile = useUpdateCompanyProfile();
  const [isEditing, setIsEditing] = useState(false);
  const [form, setForm] = useState<FormState>(toFormState(profile));

  useEffect(() => {
    if (profile) {
      setForm(toFormState(profile));
    }
  }, [profile]);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  const handleChange = (field: keyof FormState, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const payload: Partial<CompanyProfile> = {};
    (Object.keys(form) as Array<keyof FormState>).forEach((key) => {
      const value = form[key].trim();
      if (value) {
        payload[key] = value;
      } else {
        payload[key] = null;
      }
    });
    await updateProfile.mutateAsync(payload);
    setIsEditing(false);
  };

  if (authLoading || profileLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated || !user) return null;

  const displayProfile = profile!;
  const hasCompanyProfile = displayProfile && (displayProfile.company_name || displayProfile.business_type);

  return (
    <>
      <PageHero
        title="Company Information"
        subtitle="Your registered contact and company details"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "Company" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="grid lg:grid-cols-2 gap-6">
            {/* Personal Contact Details */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center">
                  <User className="w-5 h-5 text-secondary-600" />
                </div>
                <h2 className="text-lg font-bold text-text-heading">Contact Details</h2>
              </div>

              <div className="space-y-4">
                <div className="flex items-start gap-3">
                  <User className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Full Name</p>
                    <p className="font-medium text-text-heading">{user.name}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <Mail className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Email</p>
                    <p className="font-medium text-text-heading">{user.email}</p>
                  </div>
                </div>
                <div className="flex items-start gap-3">
                  <Phone className="w-5 h-5 text-muted mt-0.5" />
                  <div>
                    <p className="text-sm text-muted">Phone</p>
                    <p className="font-medium text-text-heading">{user.phone || "Not provided"}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Company Details */}
            <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-secondary-50 flex items-center justify-center">
                    <Building2 className="w-5 h-5 text-secondary-600" />
                  </div>
                  <h2 className="text-lg font-bold text-text-heading">Company Details</h2>
                </div>
                {hasCompanyProfile && !isEditing && (
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => setIsEditing(true)}
                    className="inline-flex items-center gap-2"
                  >
                    Edit
                  </Button>
                )}
              </div>

              {!hasCompanyProfile && !isEditing ? (
                <div className="py-10 text-center">
                  <Building2 className="w-14 h-14 mx-auto mb-4 text-placeholder" />
                  <h3 className="text-lg font-bold text-text-heading mb-2">Company profile not yet configured</h3>
                  <p className="text-muted mb-6 max-w-sm mx-auto">
                    Add your business information to speed up future quotations.
                  </p>
                  <Button
                    type="button"
                    onClick={() => setIsEditing(true)}
                    className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
                  >
                    <ArrowRight className="w-4 h-4" />
                    Add Company Information
                  </Button>
                </div>
              ) : isEditing ? (
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Company Name</label>
                      <input
                        type="text"
                        value={form.company_name}
                        onChange={(e) => handleChange("company_name", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Business Type</label>
                      <input
                        type="text"
                        value={form.business_type}
                        onChange={(e) => handleChange("business_type", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Industry</label>
                    <input
                      type="text"
                      value={form.industry}
                      onChange={(e) => handleChange("industry", e.target.value)}
                      className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    />
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Tax Identification (TIN)</label>
                      <input
                        type="text"
                        value={form.tax_identification}
                        onChange={(e) => handleChange("tax_identification", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Registration Number</label>
                      <input
                        type="text"
                        value={form.registration_number}
                        onChange={(e) => handleChange("registration_number", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Website</label>
                    <input
                      type="url"
                      value={form.website}
                      onChange={(e) => handleChange("website", e.target.value)}
                      className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    />
                  </div>
                  <div className="grid sm:grid-cols-3 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">District</label>
                      <input
                        type="text"
                        value={form.district}
                        onChange={(e) => handleChange("district", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">City</label>
                      <input
                        type="text"
                        value={form.city}
                        onChange={(e) => handleChange("city", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Country</label>
                      <input
                        type="text"
                        value={form.country}
                        onChange={(e) => handleChange("country", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Address</label>
                    <textarea
                      value={form.address}
                      onChange={(e) => handleChange("address", e.target.value)}
                      rows={3}
                      className="w-full rounded-xl border border-default bg-surface-page px-4 py-3 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    />
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Primary Contact Name</label>
                      <input
                        type="text"
                        value={form.primary_contact_name}
                        onChange={(e) => handleChange("primary_contact_name", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-text-heading mb-1">Primary Contact Phone</label>
                      <input
                        type="tel"
                        value={form.primary_contact_phone}
                        onChange={(e) => handleChange("primary_contact_phone", e.target.value)}
                        className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-text-heading mb-1">Primary Contact Email</label>
                    <input
                      type="email"
                      value={form.primary_contact_email}
                      onChange={(e) => handleChange("primary_contact_email", e.target.value)}
                      className="w-full rounded-xl border border-default bg-surface-page px-4 py-2.5 text-sm text-text-heading placeholder:text-placeholder focus:outline-none focus:ring-2 focus:ring-secondary-500"
                    />
                  </div>
                  <div className="flex items-center justify-end gap-3 pt-2">
                    <Button type="button" variant="outline" onClick={() => setIsEditing(false)}>
                      Cancel
                    </Button>
                    <Button
                      type="submit"
                      disabled={updateProfile.isPending}
                      className="inline-flex items-center gap-2"
                    >
                      {updateProfile.isPending ? (
                        <Loader2 className="w-4 h-4 animate-spin" />
                      ) : (
                        <Save className="w-4 h-4" />
                      )}
                      Save Changes
                    </Button>
                  </div>
                </form>
              ) : (
                <div className="space-y-4">
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <p className="text-sm text-muted">Company Name</p>
                      <p className="font-medium text-text-heading">{displayProfile.company_name || "—"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted">Business Type</p>
                      <p className="font-medium text-text-heading">{displayProfile.business_type || "—"}</p>
                    </div>
                  </div>
                  <div>
                    <p className="text-sm text-muted">Industry</p>
                    <p className="font-medium text-text-heading">{displayProfile.industry || "—"}</p>
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <p className="text-sm text-muted">Tax Identification</p>
                      <p className="font-medium text-text-heading">{displayProfile.tax_identification || "—"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted">Registration Number</p>
                      <p className="font-medium text-text-heading">{displayProfile.registration_number || "—"}</p>
                    </div>
                  </div>
                  {displayProfile.website && (
                    <div>
                      <p className="text-sm text-muted">Website</p>
                      <a
                        href={displayProfile.website}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="font-medium text-secondary-600 hover:text-secondary-700"
                      >
                        {displayProfile.website}
                      </a>
                    </div>
                  )}
                  <div className="grid sm:grid-cols-3 gap-4">
                    <div>
                      <p className="text-sm text-muted">District</p>
                      <p className="font-medium text-text-heading">{displayProfile.district || "—"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted">City</p>
                      <p className="font-medium text-text-heading">{displayProfile.city || "—"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted">Country</p>
                      <p className="font-medium text-text-heading">{displayProfile.country || "—"}</p>
                    </div>
                  </div>
                  <div>
                    <p className="text-sm text-muted">Address</p>
                    <p className="font-medium text-text-heading">{displayProfile.address || "—"}</p>
                  </div>
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div>
                      <p className="text-sm text-muted">Primary Contact</p>
                      <p className="font-medium text-text-heading">{displayProfile.primary_contact_name || "—"}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted">Primary Contact Phone</p>
                      <p className="font-medium text-text-heading">{displayProfile.primary_contact_phone || "—"}</p>
                    </div>
                  </div>
                  <div>
                    <p className="text-sm text-muted">Primary Contact Email</p>
                    <p className="font-medium text-text-heading">{displayProfile.primary_contact_email || "—"}</p>
                  </div>
                </div>
              )}
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
