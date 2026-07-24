"use client";

import { useState, FormEvent } from "react";
import Link from "next/link";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";
import { InputField, TextareaField } from "@/components/common/form-field";
import { CheckCircle, Loader2, Send, ArrowRight, Home, ShoppingBag } from "lucide-react";
import { cn } from "@/lib/utils";

const bulkBenefits = [
  {
    icon: "Tag",
    title: "Volume Pricing",
    description: "Competitive wholesale rates based on your order quantity.",
  },
  {
    icon: "Truck",
    title: "Scheduled Deliveries",
    description: "Set up recurring shipments to keep your shelves stocked.",
  },
  {
    icon: "Headphones",
    title: "Dedicated Support",
    description: "A single point of contact for quotes, orders, and delivery.",
  },
  {
    icon: "Package",
    title: "Custom Packaging",
    description: "Branded and bulk packaging options for corporate needs.",
  },
];

interface FormErrors {
  name?: string;
  business?: string;
  email?: string;
  phone?: string;
  quantity?: string;
  message?: string;
}

export function BulkOrdersPageClient() {
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<FormErrors>({});

  const validate = (formData: FormData): FormErrors => {
    const next: FormErrors = {};
    const name = formData.get("name")?.toString().trim();
    const business = formData.get("business")?.toString().trim();
    const email = formData.get("email")?.toString().trim();
    const phone = formData.get("phone")?.toString().trim();
    const quantity = formData.get("quantity")?.toString().trim();
    const message = formData.get("message")?.toString().trim();

    if (!name || name.length < 2) next.name = "Full name is required.";
    if (!business || business.length < 2) next.business = "Business name is required.";
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      next.email = "Please enter a valid email address.";
    }
    if (!phone || phone.length < 7) next.phone = "Please enter a valid phone number.";
    if (!quantity || isNaN(Number(quantity)) || Number(quantity) < 1) {
      next.quantity = "Please enter an estimated quantity.";
    }
    if (!message || message.length < 10) {
      next.message = "Please describe your requirements (at least 10 characters).";
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

    // Future integration: POST to /api/bulk-quotes
    await new Promise((resolve) => setTimeout(resolve, 800));

    setLoading(false);
    setSubmitted(true);
    e.currentTarget.reset();
  };

  return (
    <main>
      <PageHero
        title="Bulk & Corporate Orders"
        subtitle="Request a wholesale quotation for institutions, resale, or large-scale fabric care supply."
        breadcrumb={[{ label: "Bulk Orders" }]}
      />

      {/* Benefits */}
      <section className="py-20 lg:py-28 bg-white" aria-labelledby="bulk-benefits-heading">
        <Container>
          <SectionHeader
            id="bulk-benefits-heading"
            title="Why Order in Bulk?"
            subtitle="Flexible commercial solutions for businesses of every size."
          />
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {bulkBenefits.map((benefit, index) => (
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
      <section className="py-20 lg:py-28 bg-[#f8fafc]" aria-labelledby="quote-heading">
        <Container>
          <div className="max-w-3xl mx-auto">
            <div className="bg-white rounded-[24px] border border-[#e2e8f0] shadow-lg p-6 lg:p-10">
              {!submitted ? (
                <>
                  <SectionHeader
                    id="quote-heading"
                    title="Request a Quotation"
                    subtitle="Tell us what you need and our sales team will prepare a tailored quote."
                    centered={false}
                  />
                  <form onSubmit={handleSubmit} className="space-y-5 mt-8" noValidate>
                    <div className="grid sm:grid-cols-2 gap-5">
                      <InputField
                        id="name"
                        name="name"
                        label="Full Name"
                        placeholder="Your name"
                        error={errors.name}
                      />
                      <InputField
                        id="business"
                        name="business"
                        label="Business / Organisation"
                        placeholder="Registered business name"
                        error={errors.business}
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
                        id="quantity"
                        name="quantity"
                        type="number"
                        min={1}
                        label="Estimated Quantity"
                        placeholder="e.g. 500 units"
                        error={errors.quantity}
                      />
                      <InputField
                        id="product"
                        name="product"
                        label="Product of Interest (optional)"
                        placeholder="e.g. Heavy Duty Detergent"
                      />
                    </div>
                    <TextareaField
                      id="message"
                      name="message"
                      label="Requirements"
                      placeholder="Describe your timeline, delivery location, packaging needs, or any questions..."
                      rows={4}
                      error={errors.message}
                    />
                    <button
                      type="submit"
                      disabled={loading}
                      className={cn(
                        "w-full inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-all",
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
                    <p className="text-xs text-[#94a3b8] text-center">
                      This is a quotation request only. You will not be charged now.
                    </p>
                  </form>
                </>
              ) : (
                <div className="text-center py-10">
                  <div className="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
                    <CheckCircle className="w-8 h-8 text-green-600" aria-hidden="true" />
                  </div>
                  <h2 className="text-2xl font-bold text-[#0a1628] mb-2">Quote Request Received</h2>
                  <p className="text-[#64748b] mb-8">
                    Thank you for your enquiry. Our sales team will review your requirements and send
                    a tailored quotation within 1-2 business days.
                  </p>
                  <div className="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link
                      href="/"
                      className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-[#0a1628] bg-white border border-[#e2e8f0] hover:bg-[#f8fafc] transition-colors"
                    >
                      <Home className="w-4 h-4" />
                      Return Home
                    </Link>
                    <Link
                      href="/products"
                      className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all"
                    >
                      <ShoppingBag className="w-4 h-4" />
                      Continue Shopping
                      <ArrowRight className="w-4 h-4" />
                    </Link>
                  </div>
                </div>
              )}
            </div>
          </div>
        </Container>
      </section>
    </main>
  );
}
