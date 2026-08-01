"use client";

import { useState, FormEvent } from "react";
import { useRouter } from "next/navigation";
import { Loader2, Send, AlertCircle, Upload } from "lucide-react";
import { InputField, TextareaField, SelectField } from "@/components/common/form-field";
import { useDistributorMutation } from "@/hooks/use-distributor";
import { cn } from "@/lib/utils";
import type { DistributorFormData } from "@/types";

type FormErrorKey = keyof Omit<DistributorFormData, "documents"> | "documents" | "_server";

type FormErrors = Partial<Record<FormErrorKey, string>>;

const businessTypeOptions = [
  { value: "", label: "Select business type" },
  { value: "wholesaler", label: "Wholesaler" },
  { value: "retail_chain", label: "Retail Chain" },
  { value: "regional_distributor", label: "Regional Distributor" },
  { value: "supermarket", label: "Supermarket" },
  { value: "cleaning_supplier", label: "Cleaning Supplier" },
  { value: "commercial_supply", label: "Commercial Supply Company" },
  { value: "institutional_supplier", label: "Institutional Supplier" },
  { value: "entrepreneur", label: "Entrepreneur / Sole Proprietor" },
  { value: "other", label: "Other" },
];

const yesNoOptions = [
  { value: "", label: "Select an option" },
  { value: "yes", label: "Yes" },
  { value: "no", label: "No" },
  { value: "partial", label: "Partial / Shared" },
];

const fieldNameMap: Record<string, keyof DistributorFormData> = {
  company_name: "businessName",
  contact_person: "fullName",
  position: "position",
  email: "email",
  phone: "phone",
  district: "district",
  physical_address: "physicalAddress",
  years_in_business: "yearsInBusiness",
  business_type: "businessType",
  regions_covered: "regionsCovered",
  existing_brands: "existingBrands",
  warehouse_availability: "warehouseAvailability",
  delivery_capability: "deliveryCapability",
  additional_information: "additionalInformation",
  documents: "documents",
};

export function DistributorForm() {
  const [errors, setErrors] = useState<FormErrors>({});
  const [documents, setDocuments] = useState<FileList | null>(null);
  const router = useRouter();

  const mutation = useDistributorMutation();

  const getFormValue = (formData: FormData, name: string): string => {
    return formData.get(name)?.toString().trim() ?? "";
  };

  const validate = (formData: FormData, files: FileList | null): FormErrors => {
    const next: FormErrors = {};

    const fullName = getFormValue(formData, "fullName");
    const businessName = getFormValue(formData, "businessName");
    const email = getFormValue(formData, "email");
    const phone = getFormValue(formData, "phone");
    const district = getFormValue(formData, "district");
    const physicalAddress = getFormValue(formData, "physicalAddress");
    const businessType = getFormValue(formData, "businessType");
    const regionsCovered = getFormValue(formData, "regionsCovered");

    if (fullName.length < 2) next.fullName = "Full name is required.";
    if (businessName.length < 2) next.businessName = "Business name is required.";
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      next.email = "Please enter a valid email address.";
    }
    if (phone.length < 7) next.phone = "Please enter a valid phone number.";
    if (district.length < 2) next.district = "District / city is required.";
    if (physicalAddress.length < 5) next.physicalAddress = "Physical address is required.";
    if (!businessType) next.businessType = "Please select a business type.";
    if (regionsCovered.length < 2) next.regionsCovered = "Regions covered are required.";

    if (files && files.length > 5) {
      next.documents = "You can upload a maximum of 5 documents.";
    }

    if (files) {
      Array.from(files).forEach((file) => {
        if (file.size > 5 * 1024 * 1024) {
          next.documents = "Each document must be smaller than 5 MB.";
        }
        const allowed = ["application/pdf", "image/jpeg", "image/png"];
        if (!allowed.includes(file.type)) {
          next.documents = "Only PDF, JPG, and PNG files are allowed.";
        }
      });
    }

    return next;
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const validationErrors = validate(formData, documents);

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});

    const data: DistributorFormData = {
      fullName: getFormValue(formData, "fullName"),
      businessName: getFormValue(formData, "businessName"),
      position: getFormValue(formData, "position"),
      email: getFormValue(formData, "email"),
      phone: getFormValue(formData, "phone"),
      district: getFormValue(formData, "district"),
      physicalAddress: getFormValue(formData, "physicalAddress"),
      yearsInBusiness: getFormValue(formData, "yearsInBusiness"),
      businessType: getFormValue(formData, "businessType"),
      regionsCovered: getFormValue(formData, "regionsCovered"),
      existingBrands: getFormValue(formData, "existingBrands"),
      warehouseAvailability: getFormValue(formData, "warehouseAvailability"),
      deliveryCapability: getFormValue(formData, "deliveryCapability"),
      additionalInformation: getFormValue(formData, "additionalInformation"),
      documents,
    };

    mutation.mutate(data, {
      onSuccess: (response) => {
        e.currentTarget.reset();
        setDocuments(null);
        const ref = response.id ? `VESTRA-DIST-${response.id}` : "VESTRA-DIST-0000";
        router.push(`/distributor/success?ref=${encodeURIComponent(ref)}`);
      },
      onError: (error) => {
        if (error instanceof Error && "errors" in error) {
          const apiError = error as Error & { errors?: Record<string, string[]> };
          const serverErrors: FormErrors = {};
          if (apiError.errors) {
            Object.entries(apiError.errors).forEach(([key, messages]) => {
              if (messages && messages.length > 0) {
                const mappedKey = fieldNameMap[key] ?? (key as keyof DistributorFormData);
                (serverErrors as Record<string, string>)[mappedKey as string] = messages[0];
              }
            });
          }
          setErrors(serverErrors);
        } else {
          setErrors({ _server: error.message || "Something went wrong. Please try again." });
        }
      },
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-5" noValidate>
      {errors._server && (
        <div className="flex items-center gap-2 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
          <AlertCircle className="w-4 h-4 flex-shrink-0" />
          {errors._server}
        </div>
      )}

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField
          id="fullName"
          name="fullName"
          label="Full Name"
          placeholder="Your full name"
          error={errors.fullName}
        />
        <InputField
          id="businessName"
          name="businessName"
          label="Business Name"
          placeholder="Registered business name"
          error={errors.businessName}
        />
      </div>

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField
          id="position"
          name="position"
          label="Position / Title"
          placeholder="e.g. Managing Director"
          error={errors.position}
        />
        <InputField
          id="email"
          name="email"
          type="email"
          label="Email Address"
          placeholder="you@business.com"
          error={errors.email}
        />
      </div>

      <div className="grid sm:grid-cols-2 gap-5">
        <InputField
          id="phone"
          name="phone"
          type="tel"
          label="Phone Number"
          placeholder="+256 707 128 442"
          error={errors.phone}
        />
        <InputField
          id="district"
          name="district"
          label="District / City"
          placeholder="Kampala"
          error={errors.district}
        />
      </div>

      <InputField
        id="physicalAddress"
        name="physicalAddress"
        label="Physical Address"
        placeholder="Plot 123, Kampala Road"
        error={errors.physicalAddress}
      />

      <div className="grid sm:grid-cols-2 gap-5">
        <SelectField
          id="businessType"
          name="businessType"
          label="Business Type"
          options={businessTypeOptions}
          error={errors.businessType}
        />
        <InputField
          id="yearsInBusiness"
          name="yearsInBusiness"
          type="number"
          min={0}
          max={100}
          label="Years in Business"
          placeholder="e.g. 5"
          error={errors.yearsInBusiness}
        />
      </div>

      <TextareaField
        id="regionsCovered"
        name="regionsCovered"
        label="Regions Covered"
        placeholder="Which districts or regions do you currently cover?"
        rows={3}
        error={errors.regionsCovered}
      />

      <InputField
        id="existingBrands"
        name="existingBrands"
        label="Existing Brands / Product Lines (Optional)"
        placeholder="List any current distribution brands"
        error={errors.existingBrands}
      />

      <div className="grid sm:grid-cols-2 gap-5">
        <SelectField
          id="warehouseAvailability"
          name="warehouseAvailability"
          label="Warehouse Availability"
          options={yesNoOptions}
          error={errors.warehouseAvailability}
        />
        <SelectField
          id="deliveryCapability"
          name="deliveryCapability"
          label="Delivery Capability"
          options={yesNoOptions}
          error={errors.deliveryCapability}
        />
      </div>

      <TextareaField
        id="additionalInformation"
        name="additionalInformation"
        label="Additional Information (Optional)"
        placeholder="Tell us about your distribution experience, target market, and why you want to partner with VESTRA..."
        rows={4}
        error={errors.additionalInformation}
      />

      <div className="space-y-1.5">
        <label htmlFor="documents" className="block text-sm font-semibold text-text-heading">
          Supporting Documents (Optional)
        </label>
        <div
          className={cn(
            "relative flex items-center gap-3 px-4 py-3 rounded-xl border bg-neutral-50 cursor-pointer hover:bg-neutral-100 transition-colors-base",
            errors.documents ? "border-danger-400" : "border-border-default"
          )}
        >
          <Upload className="w-5 h-5 text-muted flex-shrink-0" />
          <span className="text-sm text-text-muted truncate">
            {documents && documents.length > 0
              ? `${documents.length} file${documents.length > 1 ? "s" : ""} selected`
              : "Upload business registration, trading licence, or company profile"}
          </span>
          <input
            id="documents"
            name="documents"
            type="file"
            multiple
            accept=".pdf,.jpg,.jpeg,.png"
            className="absolute inset-0 opacity-0 cursor-pointer"
            onChange={(e) => setDocuments(e.target.files)}
          />
        </div>
        {errors.documents && (
          <p id="documents-error" className="text-sm text-danger-500" role="alert">
            {errors.documents}
          </p>
        )}
        <p className="text-xs text-text-muted">Maximum 5 files. PDF, JPG, or PNG up to 5 MB each.</p>
      </div>

      <button
        type="submit"
        disabled={mutation.isPending}
        className={cn(
          "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
          mutation.isPending && "opacity-70 cursor-not-allowed"
        )}
      >
        {mutation.isPending ? (
          <>
            <Loader2 className="w-4 h-4 animate-spin" />
            Submitting...
          </>
        ) : (
          <>
            <Send className="w-4 h-4" />
            Submit Application
          </>
        )}
      </button>
    </form>
  );
}
