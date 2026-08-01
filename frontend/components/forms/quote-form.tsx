"use client";

import { useState, FormEvent } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { CheckCircle, Loader2, Send, ArrowRight, Home, PackageSearch, Briefcase } from "lucide-react";
import { InputField, TextareaField } from "@/components/common/form-field";
import { QuoteItemsField } from "./quote-items-field";
import { createQuoteRequest } from "@/lib/api/quote-requests";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import type { QuoteRequestFormData, QuoteRequestItem } from "@/types";

type FormErrors = Partial<Record<keyof QuoteRequestFormData | "items" | "general", string>>;

const initialItem = (prefilledProduct: string): QuoteRequestItem => ({
  product_id: null,
  product_name: prefilledProduct,
  package_size: "",
  quantity: 1,
  notes: "",
});

export function QuoteForm() {
  const searchParams = useSearchParams();
  const prefilledProduct = searchParams.get("product") ?? "";

  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [reference, setReference] = useState("");
  const [errors, setErrors] = useState<FormErrors>({});
  const [attachments, setAttachments] = useState<FileList | null>(null);

  const [formData, setFormData] = useState<QuoteRequestFormData>({
    full_name: "",
    company_name: "",
    email: "",
    phone: "",
    district: "",
    city: "",
    address: "",
    preferred_delivery_date: "",
    delivery_location: "",
    requirements: "",
    items: [initialItem(prefilledProduct)],
  });

  const updateField = <K extends keyof QuoteRequestFormData>(field: K, value: QuoteRequestFormData[K]) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const validate = (): FormErrors => {
    const next: FormErrors = {};

    if (formData.full_name.trim().length < 2) next.full_name = "Full name is required.";
    if (formData.company_name.trim().length < 2) next.company_name = "Business name is required.";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) next.email = "Please enter a valid email address.";
    if (formData.phone.trim().length < 7) next.phone = "Please enter a valid phone number.";

    if (!formData.items || formData.items.length === 0) {
      next.items = "Please add at least one product.";
    } else {
      const invalidItem = formData.items.some(
        (item) => !item.product_name.trim() || !item.quantity || item.quantity < 1
      );
      if (invalidItem) next.items = "Each product must have a name and a valid quantity.";
    }

    if (formData.preferred_delivery_date) {
      const selected = new Date(formData.preferred_delivery_date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (selected < today) next.preferred_delivery_date = "Preferred delivery date cannot be in the past.";
    }

    if (attachments) {
      if (attachments.length > 5) next.attachments = "Maximum 5 attachments allowed.";
      Array.from(attachments).forEach((file) => {
        const allowed = [
          "application/pdf",
          "image/jpeg",
          "image/png",
          "application/msword",
          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
          "application/vnd.ms-excel",
          "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ];
        if (!allowed.includes(file.type)) next.attachments = "Only PDF, JPG, PNG, DOC, DOCX, XLS, XLSX files are allowed.";
        if (file.size > 5 * 1024 * 1024) next.attachments = "Each attachment must be smaller than 5 MB.";
      });
    }

    return next;
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitted(false);
    const validationErrors = validate();

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});
    setLoading(true);

    const payload: QuoteRequestFormData = {
      ...formData,
      items: formData.items?.map((item) => ({
        ...item,
        quantity: Number(item.quantity),
      })),
      attachments,
    };

    try {
      const quote = await createQuoteRequest(payload);
      setReference(quote.reference_number);
      setSubmitted(true);
      toastSuccess("Your quotation request has been sent.");
    } catch (err) {
      const message = err instanceof Error ? err.message : "Something went wrong. Please try again.";
      setErrors({ general: message });
      toastError(message);
    } finally {
      setLoading(false);
    }
  };

  if (submitted) {
    return (
      <div className="text-center py-10">
        <div className="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
          <CheckCircle className="w-8 h-8 text-green-600" aria-hidden="true" />
        </div>
        <h2 className="text-2xl font-bold text-text-heading mb-2">Quote Request Received</h2>
        <p className="text-text-muted mb-2">
          Thank you. Your quotation request <strong>{reference}</strong> has been received.
        </p>
        <p className="text-text-muted mb-8">Our sales team will contact you within 24–48 business hours.</p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <Button asChild variant="outline" className="rounded-full px-6 py-3 h-auto" leftIcon={<Home className="w-4 h-4" />}>
            <Link href="/">Return Home</Link>
          </Button>
          <Button asChild variant="gradient" className="rounded-full px-6 py-3 h-auto" leftIcon={<PackageSearch className="w-4 h-4" />} rightIcon={<ArrowRight className="w-4 h-4" />}>
            <Link href="/products">View Products</Link>
          </Button>
          <Button asChild variant="outline" className="rounded-full px-6 py-3 h-auto" leftIcon={<Briefcase className="w-4 h-4" />}>
            <Link href="/distributor">Become a Distributor</Link>
          </Button>
        </div>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-8" noValidate>
      {errors.general && (
        <p className="text-sm text-danger-600 text-center" role="alert">
          {errors.general}
        </p>
      )}

      {/* Customer Information */}
      <div className="space-y-5">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Customer Information
        </h3>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="full_name"
            name="full_name"
            label="Full Name"
            placeholder="Your name"
            value={formData.full_name}
            onChange={(e) => updateField("full_name", e.target.value)}
            error={errors.full_name}
          />
          <InputField
            id="company_name"
            name="company_name"
            label="Business / Organisation"
            placeholder="Registered business name"
            value={formData.company_name}
            onChange={(e) => updateField("company_name", e.target.value)}
            error={errors.company_name}
          />
        </div>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="email"
            name="email"
            type="email"
            label="Email Address"
            placeholder="you@business.com"
            value={formData.email}
            onChange={(e) => updateField("email", e.target.value)}
            error={errors.email}
          />
          <InputField
            id="phone"
            name="phone"
            type="tel"
            label="Phone Number"
            placeholder="+256 707 128 442"
            value={formData.phone}
            onChange={(e) => updateField("phone", e.target.value)}
            error={errors.phone}
          />
        </div>
      </div>

      {/* Business Information */}
      <div className="space-y-5">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Business Information
        </h3>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="district"
            name="district"
            label="District"
            placeholder="e.g. Kampala"
            value={formData.district}
            onChange={(e) => updateField("district", e.target.value)}
          />
          <InputField
            id="city"
            name="city"
            label="City / Town"
            placeholder="e.g. Nakawa"
            value={formData.city}
            onChange={(e) => updateField("city", e.target.value)}
          />
        </div>
        <TextareaField
          id="address"
          name="address"
          label="Physical Address"
          placeholder="Street address, plot number, landmark..."
          rows={3}
          value={formData.address}
          onChange={(e) => updateField("address", e.target.value)}
        />
      </div>

      {/* Product Requirements */}
      <div className="space-y-5">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Product Requirements
        </h3>
        <QuoteItemsField
          items={formData.items ?? []}
          onChange={(items) => updateField("items", items)}
          error={errors.items}
        />
      </div>

      {/* Delivery Requirements */}
      <div className="space-y-5">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Delivery Requirements
        </h3>
        <div className="grid sm:grid-cols-2 gap-5">
          <InputField
            id="preferred_delivery_date"
            name="preferred_delivery_date"
            type="date"
            label="Preferred Delivery Date"
            value={formData.preferred_delivery_date}
            onChange={(e) => updateField("preferred_delivery_date", e.target.value)}
            error={errors.preferred_delivery_date}
          />
          <InputField
            id="delivery_location"
            name="delivery_location"
            label="Delivery Location"
            placeholder="Where should we deliver?"
            value={formData.delivery_location}
            onChange={(e) => updateField("delivery_location", e.target.value)}
          />
        </div>
      </div>

      {/* Additional Notes */}
      <div className="space-y-5">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Additional Notes
        </h3>
        <TextareaField
          id="requirements"
          name="requirements"
          label="Requirements / Questions"
          placeholder="Describe your timeline, packaging needs, or any questions..."
          rows={4}
          value={formData.requirements}
          onChange={(e) => updateField("requirements", e.target.value)}
        />
      </div>

      {/* Attachments */}
      <div className="space-y-3">
        <h3 className="text-lg font-bold text-text-heading border-b border-border-default pb-2">
          Attachments
        </h3>
        <label
          htmlFor="attachments"
          className={cn(
            "relative flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-xl border-2 border-dashed bg-neutral-50 cursor-pointer hover:bg-neutral-100 transition-colors-base text-center",
            errors.attachments ? "border-danger-400" : "border-border-default"
          )}
        >
          <span className="text-sm font-semibold text-text-heading">
            {attachments && attachments.length > 0
              ? `${attachments.length} file${attachments.length > 1 ? "s" : ""} selected`
              : "Click to upload purchase orders, requirement documents, or images"}
          </span>
          <span className="text-xs text-text-muted">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX up to 5 MB each. Max 5.</span>
          <input
            id="attachments"
            name="attachments"
            type="file"
            multiple
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
            className="absolute inset-0 opacity-0 cursor-pointer"
            onChange={(e) => setAttachments(e.target.files)}
          />
        </label>
        {errors.attachments && <p className="text-sm text-danger-500">{errors.attachments}</p>}
      </div>

      <button
        type="submit"
        disabled={loading}
        className={cn(
          "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all-base",
          loading && "opacity-70 cursor-not-allowed"
        )}
      >
        {loading ? (
          <>
            <Loader2 className="w-4 h-4 animate-spin" />
            Sending Request...
          </>
        ) : (
          <>
            <Send className="w-4 h-4" />
            Request My Quote
          </>
        )}
      </button>
      <p className="text-xs text-placeholder text-center">This is a quotation request only. You will not be charged now.</p>
    </form>
  );
}
