"use client";

import { useState } from "react";
import { Building2, Loader2, Save, Upload, X } from "lucide-react";
import { InputField, TextareaField, SelectField } from "@/components/common/form-field";
import { useDistributorProfile } from "@/hooks/use-distributor-profile";
import { toastSuccess, toastError } from "@/lib/toast-utils";
import { cn } from "@/lib/utils";
import Image from "next/image";
import type { Distributor } from "@/types";

const businessTypes = [
  { value: "", label: "Select business type" },
  { value: "retailer", label: "Retailer" },
  { value: "wholesaler", label: "Wholesaler" },
  { value: "distributor", label: "Distributor" },
  { value: "laundry", label: "Commercial Laundry" },
  { value: "other", label: "Other" },
];

const companySizes = [
  { value: "", label: "Select company size" },
  { value: "1-10", label: "1-10 employees" },
  { value: "11-50", label: "11-50 employees" },
  { value: "51-200", label: "51-200 employees" },
  { value: "200+", label: "200+ employees" },
];

export function CompanyPageClient() {
  const { data: distributor, isLoading, update, uploadLogo, removeLogo } = useDistributorProfile();
  const [form, setForm] = useState<Partial<Distributor>>({});
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  if (isLoading || !distributor) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-green-500" />
      </div>
    );
  }

  const handleChange = (field: keyof typeof distributor, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files?.[0]) setLogoFile(e.target.files[0]);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    try {
      await update(form);
      if (logoFile) await uploadLogo(logoFile);
      setForm({});
      setLogoFile(null);
      toastSuccess("Company profile updated.");
    } catch (err) {
      toastError(err instanceof Error ? err.message : "Update failed.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-extrabold text-[#0a1628]">Company Profile</h1>
        <p className="text-[#64748b]">Manage your distributor company information.</p>
      </div>

      <form onSubmit={handleSubmit} className="bg-white rounded-[20px] border border-[#e2e8f0] shadow-sm p-6 lg:p-8 space-y-6">
        <div className="flex flex-col sm:flex-row items-start gap-6 pb-6 border-b border-[#e2e8f0]">
          <div className="relative w-24 h-24 rounded-2xl bg-[#f8fafc] border border-[#e2e8f0] overflow-hidden flex items-center justify-center">
            {distributor.logo_url && !logoFile ? (
              <Image src={distributor.logo_url} alt={distributor.company_name} fill className="object-cover" />
            ) : (
              <Building2 className="w-10 h-10 text-[#94a3b8]" />
            )}
          </div>
          <div className="flex-1">
            <h3 className="text-lg font-bold text-[#0a1628] mb-2">Company Logo</h3>
            <div className="flex flex-wrap items-center gap-3">
              <label className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 cursor-pointer transition-colors">
                <Upload className="w-4 h-4" />
                {logoFile ? logoFile.name : "Upload Logo"}
                <input type="file" accept="image/*" className="hidden" onChange={handleLogoChange} />
              </label>
              {distributor.logo_url && (
                <button
                  type="button"
                  onClick={() => removeLogo()}
                  className="inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-50 font-semibold rounded-xl hover:bg-red-100 transition-colors"
                >
                  <X className="w-4 h-4" />
                  Remove
                </button>
              )}
            </div>
          </div>
        </div>

        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="company_name"
            label="Company Name"
            value={form.company_name ?? distributor.company_name}
            onChange={(e) => handleChange("company_name", e.target.value)}
          />
          <InputField
            id="trading_name"
            label="Trading Name"
            value={form.trading_name ?? distributor.trading_name ?? ""}
            onChange={(e) => handleChange("trading_name", e.target.value)}
          />
          <InputField
            id="registration_number"
            label="Registration Number"
            value={form.registration_number ?? distributor.registration_number ?? ""}
            onChange={(e) => handleChange("registration_number", e.target.value)}
          />
          <InputField
            id="tax_identification"
            label="Tax Identification"
            value={form.tax_identification ?? distributor.tax_identification ?? ""}
            onChange={(e) => handleChange("tax_identification", e.target.value)}
          />
          <SelectField
            id="business_type"
            label="Business Type"
            options={businessTypes}
            value={form.business_type ?? distributor.business_type ?? ""}
            onChange={(e) => handleChange("business_type", e.target.value)}
          />
          <SelectField
            id="company_size"
            label="Company Size"
            options={companySizes}
            value={form.company_size ?? distributor.company_size ?? ""}
            onChange={(e) => handleChange("company_size", e.target.value)}
          />
          <InputField
            id="website"
            label="Website"
            value={form.website ?? distributor.website ?? ""}
            onChange={(e) => handleChange("website", e.target.value)}
          />
          <InputField
            id="years_in_business"
            label="Years in Business"
            type="number"
            value={form.years_in_business?.toString() ?? distributor.years_in_business?.toString() ?? ""}
            onChange={(e) => handleChange("years_in_business", e.target.value)}
          />
        </div>

        <div className="border-t border-[#e2e8f0] pt-6">
          <h3 className="text-lg font-bold text-[#0a1628] mb-4">Contact Information</h3>
          <div className="grid sm:grid-cols-2 gap-5">
            <InputField
              id="primary_contact_name"
              label="Primary Contact Name"
              value={form.primary_contact_name ?? distributor.primary_contact_name ?? ""}
              onChange={(e) => handleChange("primary_contact_name", e.target.value)}
            />
            <InputField
              id="email"
              label="Email"
              type="email"
              value={form.email ?? distributor.email}
              onChange={(e) => handleChange("email", e.target.value)}
            />
            <InputField
              id="phone"
              label="Phone"
              type="tel"
              value={form.phone ?? distributor.phone ?? ""}
              onChange={(e) => handleChange("phone", e.target.value)}
            />
          </div>
        </div>

        <div className="border-t border-[#e2e8f0] pt-6">
          <h3 className="text-lg font-bold text-[#0a1628] mb-4">Address</h3>
          <div className="grid sm:grid-cols-2 gap-5">
            <InputField
              id="country"
              label="Country"
              value={form.country ?? distributor.country ?? ""}
              onChange={(e) => handleChange("country", e.target.value)}
            />
            <InputField
              id="district"
              label="District"
              value={form.district ?? distributor.district ?? ""}
              onChange={(e) => handleChange("district", e.target.value)}
            />
            <InputField
              id="city"
              label="City"
              value={form.city ?? distributor.city ?? ""}
              onChange={(e) => handleChange("city", e.target.value)}
            />
            <InputField
              id="postal_address"
              label="Postal Address"
              value={form.postal_address ?? distributor.postal_address ?? ""}
              onChange={(e) => handleChange("postal_address", e.target.value)}
            />
            <TextareaField
              id="address"
              label="Physical Address"
              value={form.address ?? distributor.address ?? ""}
              onChange={(e) => handleChange("address", e.target.value)}
              className="sm:col-span-2"
            />
          </div>
        </div>

        <div className="flex justify-end pt-4">
          <button
            type="submit"
            disabled={isSaving}
            className={cn(
              "inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors",
              isSaving && "opacity-70 cursor-not-allowed"
            )}
          >
            {isSaving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
            {isSaving ? "Saving..." : "Save Changes"}
          </button>
        </div>
      </form>
    </div>
  );
}
