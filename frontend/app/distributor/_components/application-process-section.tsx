"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";

const steps = [
  { number: "01", title: "Submit Application", description: "Complete the form and share your business profile." },
  { number: "02", title: "Sales Review", description: "Our team assesses your market fit and capacity." },
  { number: "03", title: "Business Verification", description: "We verify registration, location, and capabilities." },
  { number: "04", title: "Partnership Discussion", description: "We discuss terms, territory, and expectations." },
  { number: "05", title: "Approval", description: "Receive formal approval and a distribution agreement." },
  { number: "06", title: "Onboarding", description: "Get training, materials, and your first order support." },
];

export function ApplicationProcessSection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="process-heading">
      <Container>
        <SectionHeader
          id="process-heading"
          title="Application Process"
          subtitle="A clear, transparent path from application to active partnership."
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
