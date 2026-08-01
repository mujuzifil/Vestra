"use client";

import { Suspense } from "react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { QuoteForm } from "@/components/forms/quote-form";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";
import { QuoteHero } from "./_components/quote-hero";
import { WhoCanRequestSection } from "./_components/who-can-request-section";
import { WhyQuoteSection } from "./_components/why-quote-section";
import { QuoteProcessSection } from "./_components/quote-process-section";
import { CustomerSupportSection } from "./_components/customer-support-section";
import { RelatedResourcesSection } from "./_components/related-resources-section";
import { FinalCTASection } from "./_components/final-cta-section";

const quoteFaqs = [
  {
    question: "What is the minimum order quantity?",
    answer: "Minimum quantities vary by product. Our team will confirm MOQ and volume pricing in your quotation.",
  },
  {
    question: "How long does it take to receive a quote?",
    answer: "We typically respond within 24–48 business hours for standard requests.",
  },
  {
    question: "Do you deliver nationwide?",
    answer: "Yes. We arrange delivery across Uganda and will include logistics options in your quotation.",
  },
  {
    question: "What payment terms are available?",
    answer: "Payment terms depend on order value and customer history. Options include bank transfer, mobile money, and approved credit accounts.",
  },
  {
    question: "Can I request branded or custom packaging?",
    answer: "Yes. Mention your packaging requirements in the additional notes and our team will advise on feasibility and minimums.",
  },
  {
    question: "Can I request multiple products in one quote?",
    answer: "Absolutely. Use the repeatable product rows to add as many products as you need.",
  },
  {
    question: "Who should I contact for support?",
    answer: "Call or WhatsApp +256 707 128 442, or email vestradetergent@gmail.com.",
  },
];

export function RequestQuotePageClient() {
  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "Request a Quote", url: "https://vestradetergents.com/request-quote" },
        ])}
      />
      <main>
        <QuoteHero />
        <WhoCanRequestSection />
        <WhyQuoteSection />
        <QuoteProcessSection />

        {/* Quote Form */}
        <section id="quote-form" className="py-20 lg:py-28 bg-surface-page" aria-labelledby="quote-form-heading">
          <Container>
            <div className="max-w-3xl mx-auto">
              <div className="bg-white rounded-[24px] border border-default shadow-lg p-6 lg:p-10">
                <SectionHeader
                  id="quote-form-heading"
                  title="Request Your Quote"
                  subtitle="Complete the form below. All fields marked by validation are required unless indicated optional."
                  centered={false}
                />
                <Suspense
                  fallback={
                    <div className="bg-white rounded-[24px] border border-default shadow-lg p-10 h-96 animate-pulse" />
                  }
                >
                  <QuoteForm />
                </Suspense>
              </div>
            </div>
          </Container>
        </section>

        <CustomerSupportSection />

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="quote-faq-heading">
          <Container>
            <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
              <div>
                <SectionHeader
                  id="quote-faq-heading"
                  title="Frequently Asked Questions"
                  subtitle="Common questions about requesting a quote."
                  centered={false}
                />
              </div>
              <FAQAccordion items={quoteFaqs} />
            </div>
          </Container>
        </section>

        <RelatedResourcesSection />
        <FinalCTASection />
      </main>
    </>
  );
}
