"use client";

import { useState, FormEvent } from "react";
import { useRouter } from "next/navigation";
import { Loader2, Send, AlertCircle } from "lucide-react";
import { InputField, TextareaField, SelectField } from "@/components/common/form-field";
import { useDistributorMutation } from "@/hooks/use-distributor";
import { cn } from "@/lib/utils";

interface FormErrors {
  fullName?: string;
  businessName?: string;
  email?: string;
  phone?: string;
  city?: string;
  businessType?: string;
  experience?: string;
  _server?: string;
}

const businessTypeOptions = [
  { value: "", label: "Select business type" },
  { value: "retailer", label: "Retailer" },
  { value: "wholesaler", label: "Wholesaler" },
  { value: "laundry", label: "Commercial Laundry" },
  { value: "other", label: "Other" },
];

export function DistributorForm() {
  const [errors, setErrors] = useState<FormErrors>({});
  const router = useRouter();

  const mutation = useDistributorMutation();

  const validate = (formData: FormData): FormErrors => {
    const next: FormErrors = {};
    const fullName = formData.get("fullName")?.toString().trim();
    const businessName = formData.get("businessName")?.toString().trim();
    const email = formData.get("email")?.toString().trim();
    const phone = formData.get("phone")?.toString().trim();
    const city = formData.get("city")?.toString().trim();
    const businessType = formData.get("businessType")?.toString();
    const experience = formData.get("experience")?.toString().trim();

    if (!fullName || fullName.length < 2) next.fullName = "Full name is required.";
    if (!businessName || businessName.length < 2) next.businessName = "Business name is required.";
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      next.email = "Please enter a valid email address.";
    }
    if (!phone || phone.length < 7) next.phone = "Please enter a valid phone number.";
    if (!city || city.length < 2) next.city = "City / region is required.";
    if (!businessType) next.businessType = "Please select a business type.";
    if (!experience || experience.length < 10) {
      next.experience = "Please tell us about your business (at least 10 characters).";
    }

    return next;
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const validationErrors = validate(formData);

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});

    const data = {
      fullName: formData.get("fullName")?.toString().trim() || "",
      businessName: formData.get("businessName")?.toString().trim() || "",
      email: formData.get("email")?.toString().trim() || "",
      phone: formData.get("phone")?.toString().trim() || "",
      city: formData.get("city")?.toString().trim() || "",
      businessType: formData.get("businessType")?.toString() || "",
      experience: formData.get("experience")?.toString().trim() || "",
    };

    mutation.mutate(data, {
      onSuccess: (response) => {
        e.currentTarget.reset();
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
                const mappedKey = key === "full_name" ? "fullName" : key === "business_name" ? "businessName" : key === "business_type" ? "businessType" : key;
                (serverErrors as Record<string, string>)[mappedKey] = messages[0];
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
          placeholder="Your name"
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
          id="email"
          name="email"
          type="email"
          label="Email Address"
          placeholder="you@business.com"
          error={errors.email}
        />
        <InputField
          id="phone"
          name="phone"
          type="tel"
          label="Phone Number"
          placeholder="+256 707 128 442"
          error={errors.phone}
        />
      </div>
      <InputField
        id="city"
        name="city"
        label="City / Region"
        placeholder="Kampala"
        error={errors.city}
      />
      <SelectField
        id="businessType"
        name="businessType"
        label="Business Type"
        options={businessTypeOptions}
        error={errors.businessType}
      />
      <TextareaField
        id="experience"
        name="experience"
        label="Business Experience"
        placeholder="Tell us about your distribution experience and target market..."
        rows={4}
        error={errors.experience}
      />
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
