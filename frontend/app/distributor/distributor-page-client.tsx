"use client";

import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { CTASection } from "@/components/common/cta-section";
import { ValueCard } from "@/components/common/value-card";
import { AnimatedSection } from "@/components/common/animated-section";
import { DistributorForm } from "@/components/forms/distributor-form";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";

const distributorBenefits = [
  {
    icon: "Award",
    title: "Exclusive Territory",
    description: "Gain protected distribution rights in your region.",
  },
  {
    icon: "Truck",
    title: "Reliable Supply",
    description: "Consistent product availability and timely deliveries.",
  },
  {
    icon: "BadgeCheck",
    title: "Marketing Support",
    description: "Access branded materials and promotional resources.",
  },
  {
    icon: "Sparkles",
    title: "Training",
    description: "Receive product knowledge and sales training from our team.",
  },
];

const distributorFaqs = [
  {
    question: "What is the minimum order quantity?",
    answer:
      "Minimum order quantities vary by product. Our team will share the full price list and MOQ after reviewing your application.",
  },
  {
    question: "Do I need a registered business to apply?",
    answer:
      "Yes, we prefer working with registered businesses that have experience in distribution or retail.",
  },
  {
    question: "How long does the approval process take?",
    answer: "Applications are typically reviewed within 5-7 business days.",
  },
  {
    question: "What support do distributors receive?",
    answer:
      "Distributors receive marketing materials, training, territory protection, and dedicated account support.",
  },
];

export function DistributorPageClient() {
  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestra.com/" },
          { name: "Become a Distributor", url: "https://vestra.com/distributor" },
        ])}
      />
      <main>
        <PageHero
          title="Become a Distributor"
          subtitle="Join the VESTRA network and bring professional fabric care solutions to your market."
          breadcrumb={[{ label: "Distributor" }]}
        />

        {/* Benefits */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="benefits-heading">
          <Container>
            <SectionHeader
              id="benefits-heading"
              title="Why Partner with VESTRA"
              subtitle="We support our distributors with the tools, training, and products they need to succeed."
            />
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {distributorBenefits.map((benefit, index) => (
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

        {/* Opportunities & Form */}
        <section className="py-20 lg:py-28 bg-[#f8fafc]" aria-labelledby="application-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 items-center">
              <AnimatedSection direction="left">
                <SectionHeader
                  id="application-heading"
                  title="Business Opportunities"
                  subtitle="Tailored partnership models for every scale of operation."
                  centered={false}
                />
                <ul className="space-y-4">
                  {[
                    "Retail distribution in supermarkets and shops",
                    "Supply to hotels, hospitals, and commercial laundries",
                    "Regional wholesale partnerships",
                    "Bulk corporate and institutional contracts",
                  ].map((item, index) => (
                    <li
                      key={item}
                      className="flex items-start gap-4 text-[#475569] text-base lg:text-lg"
                    >
                      <span className="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                        {index + 1}
                      </span>
                      {item}
                    </li>
                  ))}
                </ul>
              </AnimatedSection>

              <AnimatedSection
                direction="right"
                className="p-6 lg:p-10 rounded-[24px] bg-white border border-[#e2e8f0] shadow-lg"
              >
                <h2 className="text-xl lg:text-2xl font-bold text-[#0a1628] mb-2">
                  Distributor Application
                </h2>
                <p className="text-[#64748b] mb-6">
                  Complete the form below to start your partnership journey.
                </p>
                <DistributorForm />
              </AnimatedSection>
            </div>
          </Container>
        </section>

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="distributor-faq-heading">
          <Container>
            <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
              <div>
                <SectionHeader
                  id="distributor-faq-heading"
                  title="Distributor FAQ"
                  subtitle="Common questions about partnering with VESTRA."
                  centered={false}
                />
              </div>
              <FAQAccordion items={distributorFaqs} />
            </div>
          </Container>
        </section>

        <CTASection
          title="Ready to grow with us?"
          description="Reach out directly and our partnership team will guide you through the next steps."
          buttonText="Contact Us"
          buttonHref="/contact"
        />
      </main>
    </>
  );
}
