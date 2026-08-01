"use client";

import { useState, FormEvent, Suspense } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";
import { InputField, TextareaField } from "@/components/common/form-field";
import { CheckCircle, Loader2, Send, ArrowRight, Home, PackageSearch } from "lucide-react";
import { cn } from "@/lib/utils";
import { createQuoteRequest } from "@/lib/api/quote-requests";
import { toastError, toastSuccess } from "@/lib/toast-utils";
import type { QuoteRequestFormData } from "@/types";

const quoteBenefits = [
  {
    icon: "Tag",
    title: "Volume Pricing",
    description: "Competitive wholesale rates based on your order quantity and frequency.",
  },
  {
    icon: "Truck",
    title: "Scheduled Deliveries",
    description: "Set up recurring shipments to keep your operations running smoothly.",
  },
  {
    icon: "Headphones",
    title: "Dedicated Support",
    description: "A single point of contact for quotes, orders, and delivery coordination.",
  },
  {
    icon: "Package",
    title: "Custom Solutions",
    description: "Branded packaging, institutional sizing, and tailored supply programmes.",
  },
];

interface FormErrors {
  full_name?: string;
  company_name?: string;
  email?: string;
  phone?: string;
  district?: string;
  city?: string;
  address?: string;
  preferred_delivery_date?: string;
  delivery_location?: string;
  product_name?: string;
  package_size?: string;
  quantity?: string;
  requirements?: string;
  general?: string;
}

function QuoteForm() {
  const searchParams = useSearchParams();
  const prefilledProduct = searchParams.get("product") ?? "";

  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<FormErrors>({});
  const [reference, setReference] = useState("");

  const validate = (formData: FormData): FormErrors => {
    const next: FormErrors = {};
    const fullName = formData.get("full_name")?.toString().trim();
    const companyName = formData.get("company_name")?.toString().trim();
    const email = formData.get("email")?.toString().trim();
    const phone = formData.get("phone")?.toString().trim();
    const quantity = formData.get("quantity")?.toString().trim();

    if (!fullName || fullName.length < 2) next.full_name = "Full name is required.";
    if (!companyName || companyName.length < 2) next.company_name = "Business name is required.";
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      next.email = "Please enter a valid email address.";
    }
    if (!phone || phone.length < 7) next.phone = "Please enter a valid phone number.";
    if (!quantity || isNaN(Number(quantity)) || Number(quantity) < 1) {
      next.quantity = "Please enter an estimated quantity.";
    }

    const dateValue = formData.get("preferred_delivery_date")?.toString().trim();
    if (dateValue) {
      const selected = new Date(dateValue);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (selected < today) {
        next.preferred_delivery_date = "Preferred delivery date cannot be in the past.";
      }
    }

    return next;
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitted(false);
    const formData = new FormData(e.currentTarget);
    const validationErrors = validate(formData);

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    setErrors({});
    setLoading(true);

    const productName = formData.get("product_name")?.toString().trim() || prefilledProduct || "Not specified";
    const packageSize = formData.get("package_size")?.toString().trim();
    const quantity = Number(formData.get("quantity"));

    const payload: QuoteRequestFormData = {
      full_name: formData.get("full_name")?.toString().trim() || "",
      company_name: formData.get("company_name")?.toString().trim() || "",
      email: formData.get("email")?.toString().trim() || "",
      phone: formData.get("phone")?.toString().trim() || "",
      district: formData.get("district")?.toString().trim() || undefined,
      city: formData.get("city")?.toString().trim() || undefined,
      address: formData.get("address")?.toString().trim() || undefined,
      preferred_delivery_date: formData.get("preferred_delivery_date")?.toString().trim() || undefined,
      delivery_location: formData.get("delivery_location")?.toString().trim() || undefined,
      requirements: formData.get("requirements")?.toString().trim() || undefined,
      items: [
        {
          product_name: productName,
          package_size: packageSize || undefined,
          quantity,
          notes: formData.get("item_notes")?.toString().trim() || undefined,
        },
      ],
    };

    try {
      const quote = await createQuoteRequest(payload);
      setReference(quote.reference_number);
      setSubmitted(true);
      toastSuccess("Your quotation request has been sent.");
      e.currentTarget.reset();
    } catch (err) {
      const message = err instanceof Error ? err.message : "Something went wrong. Please try again.";
      setErrors({ general: message });
      toastError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-[24px] border border-default shadow-lg p-6 lg:p-10">
      {!submitted ? (
        <>
          <SectionHeader
            id="quote-heading"
            title="Request a Quote"
            subtitle="Tell us what you need and our sales team will prepare a tailored quotation."
            centered={false}
          />
          <form onSubmit={handleSubmit} className="space-y-5 mt-8" noValidate>
            <div className="grid sm:grid-cols-2 gap-5">
              <InputField
                id="full_name"
                name="full_name"
                label="Full Name"
                placeholder="Your name"
                error={errors.full_name}
              />
              <InputField
                id="company_name"
                name="company_name"
                label="Business / Organisation"
                placeholder="Registered business name"
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
            <div className="grid sm:grid-cols-2 gap-5">
              <InputField
                id="district"
                name="district"
                label="District"
                placeholder="e.g. Kampala"
                error={errors.district}
              />
              <InputField
                id="city"
                name="city"
                label="City / Town"
                placeholder="e.g. Nakawa"
                error={errors.city}
              />
            </div>
            <TextareaField
              id="address"
              name="address"
              label="Physical Address"
              placeholder="Street address, plot number, landmark..."
              rows={3}
              error={errors.address}
            />
            <div className="grid sm:grid-cols-2 gap-5">
              <InputField
                id="preferred_delivery_date"
                name="preferred_delivery_date"
                type="date"
                label="Preferred Delivery Date"
                error={errors.preferred_delivery_date}
              />
              <InputField
                id="delivery_location"
                name="delivery_location"
                label="Delivery Location"
                placeholder="Where should we deliver?"
                error={errors.delivery_location}
              />
            </div>
            <div className="grid sm:grid-cols-2 gap-5">
              <InputField
                id="product_name"
                name="product_name"
                label="Product of Interest"
                placeholder="e.g. Heavy Duty Detergent"
                defaultValue={prefilledProduct}
                error={errors.product_name}
              />
              <InputField
                id="package_size"
                name="package_size"
                label="Package Size"
                placeholder="e.g. 5L, 20L"
                error={errors.package_size}
              />
            </div>
            <div className="grid sm:grid-cols-2 gap-5">
              <InputField
                id="quantity"
                name="quantity"
                type="number"
                min={1}
                label="Estimated Quantity"
                placeholder="e.g. 500 units"
                error={errors.quantity}
              />
              <InputField
                id="item_notes"
                name="item_notes"
                label="Item Notes"
                placeholder="Specific variant, concentration, etc."
              />
            </div>
            <TextareaField
              id="requirements"
              name="requirements"
              label="Additional Requirements"
              placeholder="Describe your timeline, packaging needs, or any questions..."
              rows={4}
              error={errors.requirements}
            />

            {errors.general && (
              <p className="text-sm text-danger-600 text-center" role="alert">
                {errors.general}
              </p>
            )}

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
                  Request Quote
                </>
              )}
            </button>
            <p className="text-xs text-placeholder text-center">
              This is a quotation request only. You will not be charged now.
            </p>
          </form>
        </>
      ) : (
        <div className="text-center py-10">
          <div className="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
            <CheckCircle className="w-8 h-8 text-green-600" aria-hidden="true" />
          </div>
          <h2 className="text-2xl font-bold text-primary-900 mb-2">Quote Request Received</h2>
          <p className="text-muted mb-2">
            Thank you. Your quotation request <strong>{reference}</strong> has been received.
          </p>
          <p className="text-muted mb-8">
            Our sales team will contact you within 24–48 business hours.
          </p>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Link
              href="/"
              className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-primary-900 bg-white border border-default hover:bg-surface-page transition-colors-base"
            >
              <Home className="w-4 h-4" />
              Return Home
            </Link>
            <Link
              href="/products"
              className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all-base"
            >
              <PackageSearch className="w-4 h-4" />
              View Products
              <ArrowRight className="w-4 h-4" />
            </Link>
            <Link
              href="/distributor"
              className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-primary-900 bg-white border border-default hover:bg-surface-page transition-colors-base"
            >
              Become a Distributor
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </div>
      )}
    </div>
  );
}

export function RequestQuotePageClient() {
  return (
    <main>
      <PageHero
        title="Request a Quote"
        subtitle="Get a tailored quotation for bulk supply, resale, institutional orders, or distributor partnerships."
        breadcrumb={[{ label: "Request a Quote" }]}
      />

      {/* Benefits */}
      <section className="py-20 lg:py-28 bg-white" aria-labelledby="quote-benefits-heading">
        <Container>
          <SectionHeader
            id="quote-benefits-heading"
            title="Why Request a Quote?"
            subtitle="Flexible commercial solutions for businesses of every size."
          />
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {quoteBenefits.map((benefit, index) => (
              <ValueCard
                key={benefit.title}
                icon={benefit.icon}
                title={benefit.title}
                description={benefit.description}
                index={index}
              />
            ))}
          </div>
        </Container>
      </section>

      {/* Quote Form */}
      <section className="py-20 lg:py-28 bg-surface-page" aria-labelledby="quote-heading">
        <Container>
          <div className="max-w-3xl mx-auto">
            <Suspense fallback={<div className="bg-white rounded-[24px] border border-default shadow-lg p-10 h-96 animate-pulse" />}>
              <QuoteForm />
            </Suspense>
          </div>
        </Container>
      </section>
    </main>
  );
}
