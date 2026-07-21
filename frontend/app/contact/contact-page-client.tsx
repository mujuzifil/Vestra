"use client";

import { MapPin } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ContactCard } from "@/components/common/contact-card";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { CTASection } from "@/components/common/cta-section";
import { AnimatedSection } from "@/components/common/animated-section";
import { ContactForm } from "@/components/forms/contact-form";
import { ApiError } from "@/components/ui/api-error";
import { useContactInfo } from "@/hooks/use-settings";
import { JsonLd, contactPageSchema } from "@/lib/structured-data";

const generalFaqs = [
  {
    question: "Where can I buy VESTRA products?",
    answer:
      "VESTRA products are available through authorized distributors, select retail stores, and our online store once launched.",
  },
  {
    question: "Are VESTRA products safe for all fabrics?",
    answer:
      "Each product is formulated for specific fabric types. Please check the product label or page for recommended usage.",
  },
  {
    question: "Do you offer bulk or commercial pricing?",
    answer:
      "Yes. We offer competitive pricing for commercial laundries, hotels, and bulk buyers. Contact us for a quote.",
  },
  {
    question: "How can I become a distributor?",
    answer:
      "Fill out the distributor application form on our Become a Distributor page and our team will get in touch.",
  },
];

export function ContactPageClient() {
  const { contactInfo, isLoading, error } = useContactInfo();

  return (
    <>
      <JsonLd data={contactPageSchema()} />
      <main>
        <PageHero
          title="Contact Us"
          subtitle="We'd love to hear from you. Reach out for support, partnerships, or general inquiries."
          breadcrumb={[{ label: "Contact Us" }]}
        />

        {/* Contact Info */}
        <section className="py-16 lg:py-24 bg-white" aria-labelledby="contact-info-heading">
          <Container>
            <h2 id="contact-info-heading" className="sr-only">
              Contact Information
            </h2>
            {isLoading ? (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {Array.from({ length: 4 }).map((_, i) => (
                  <div key={i} className="h-32 rounded-[20px] bg-[#e2e8f0] animate-pulse" />
                ))}
              </div>
            ) : error ? (
              <ApiError onRetry={() => window.location.reload()} />
            ) : (
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <ContactCard
                  icon="Phone"
                  title="Phone"
                  lines={[
                    {
                      label: "Call us",
                      value: contactInfo?.phone || "+256 707 128 442",
                      href: `tel:${(contactInfo?.phone || "+256 707 128 442").replace(/\s/g, "")}`,
                    },
                  ]}
                />
                <ContactCard
                  icon="Mail"
                  title="Email"
                  lines={[
                    {
                      label: "Email us",
                      value: contactInfo?.email || "vestradetergent@gmail.com",
                      href: `mailto:${contactInfo?.email || "vestradetergent@gmail.com"}`,
                    },
                  ]}
                />
                <ContactCard
                  icon="MessageCircle"
                  title="WhatsApp"
                  lines={[
                    {
                      label: "Chat with us",
                      value: contactInfo?.whatsapp || "+256 707 128 442",
                      href: `https://wa.me/${(contactInfo?.whatsapp || "+256 707 128 442").replace(/\s/g, "")}`,
                    },
                  ]}
                />
                <ContactCard
                  icon="Clock"
                  title="Business Hours"
                  lines={[{ value: contactInfo?.businessHours || "Mon - Fri: 8:00 AM - 6:00 PM" }]}
                />
              </div>
            )}
          </Container>
        </section>

        {/* Form & Map */}
        <section className="py-16 lg:py-24 bg-[#f8fafc]" aria-labelledby="contact-form-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-8 lg:gap-12">
              <AnimatedSection
                direction="left"
                className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg"
              >
                <h2
                  id="contact-form-heading"
                  className="text-xl lg:text-2xl font-bold text-[#0a1628] mb-2"
                >
                  Send us a Message
                </h2>
                <p className="text-[#64748b] mb-6">
                  Fill out the form below and our team will get back to you shortly.
                </p>
                <ContactForm />
              </AnimatedSection>

              <AnimatedSection
                direction="right"
                className="rounded-[24px] overflow-hidden border border-[#e2e8f0] bg-white shadow-lg h-full min-h-[360px] flex flex-col"
              >
                <div className="p-6 border-b border-[#e2e8f0]">
                  <div className="flex items-start gap-3">
                    <MapPin className="w-6 h-6 text-green-500 flex-shrink-0" aria-hidden="true" />
                    <div>
                      <h3 className="text-lg font-bold text-[#0a1628]">Our Location</h3>
                      <p className="text-[#64748b]">{contactInfo?.location || "Kampala, Uganda"}</p>
                    </div>
                  </div>
                </div>
                <div className="flex-1 bg-[#e2e8f0] flex items-center justify-center p-8">
                  <div className="text-center">
                    <MapPin className="w-12 h-12 text-[#94a3b8] mx-auto mb-3" aria-hidden="true" />
                    <p className="text-[#64748b] font-medium">Google Maps placeholder</p>
                    <p className="text-sm text-[#94a3b8]">{contactInfo?.location || "Kampala, Uganda"}</p>
                  </div>
                </div>
              </AnimatedSection>
            </div>
          </Container>
        </section>

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="faq-heading">
          <Container>
            <SectionHeader
              id="faq-heading"
              title="Frequently Asked Questions"
              subtitle="Find answers to common questions about VESTRA products and services."
            />
            <div className="max-w-3xl mx-auto">
              <FAQAccordion items={generalFaqs} />
            </div>
          </Container>
        </section>

        <CTASection
          title="Prefer to chat?"
          description="Message us on WhatsApp for quick responses to your questions."
          buttonText="Chat on WhatsApp"
          buttonHref={`https://wa.me/${(contactInfo?.whatsapp || "+256 707 128 442").replace(/\s/g, "")}`}
        />
      </main>
    </>
  );
}
