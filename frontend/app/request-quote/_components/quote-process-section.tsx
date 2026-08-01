"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";

const steps = [
  { number: "01", title: "Submit Request", description: "Share your requirements through the form below." },
  { number: "02", title: "Sales Review", description: "Our team reviews your products, quantities, and delivery needs." },
  { number: "03", title: "Requirements Discussion", description: "We clarify specifications, packaging, and timeline." },
  { number: "04", title: "Quotation Preparation", description: "We prepare a formal quotation with pricing and terms." },
  { number: "05", title: "Customer Approval", description: "You review and approve the quotation." },
  { number: "06", title: "Order Processing", description: "We schedule production and delivery." },
];

export function QuoteProcessSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="quote-process-heading">
      <Container>
        <SectionHeader
          id="quote-process-heading"
          title="Quote Process"
          subtitle="A clear path from enquiry to order."
        />
        <div className="relative">
          <div className="hidden lg:block absolute top-8 left-0 right-0 h-0.5 bg-gradient-to-r from-primary-200 via-secondary-300 to-primary-200" />
          <div className="grid sm:grid-cols-2 lg:grid-cols-6 gap-8 lg:gap-6">
            {steps.map((step, index) => (
              <AnimatedItem key={step.number} delay={index * 0.1} className="relative">
                <div className="flex flex-col items-center text-center">
                  <div className="relative z-10 w-16 h-16 rounded-full bg-gradient-to-br from-primary-900 to-primary-700 text-white flex items-center justify-center text-lg font-bold shadow-lg mb-5">
                    {step.number}
                  </div>
                  <h3 className="text-lg font-bold text-text-heading mb-2">{step.title}</h3>
                  <p className="text-sm lg:text-base text-text-muted leading-relaxed max-w-xs">
                    {step.description}
                  </p>
                </div>
              </AnimatedItem>
            ))}
          </div>
        </div>
      </Container>
    </section>
  );
}
