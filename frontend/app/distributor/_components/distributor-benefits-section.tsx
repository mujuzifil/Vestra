"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";
import { cn } from "@/lib/utils";

const benefits = [
  {
    icon: "MapPin",
    title: "Territory Opportunities",
    description: "Secure defined distribution areas with controlled channel coverage.",
  },
  {
    icon: "Scale",
    title: "Bulk Pricing",
    description: "Access volume-based price structures that protect your margins.",
  },
  {
    icon: "Users",
    title: "Product Training",
    description: "Learn product benefits, usage, and sales positioning from our team.",
  },
  {
    icon: "FileText",
    title: "Sales Materials",
    description: "Receive catalogues, brochures, POS materials, and digital assets.",
  },
  {
    icon: "Headphones",
    title: "Priority Support",
    description: "Get fast responses from a dedicated distributor support line.",
  },
  {
    icon: "Package",
    title: "Reliable Inventory",
    description: "Plan confidently with consistent stock availability.",
  },
];

export function DistributorBenefitsSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="distributor-benefits-heading">
      <Container>
        <SectionHeader
          id="distributor-benefits-heading"
          title="Distributor Benefits"
          subtitle="Everything you need to build a successful VESTRA® distribution business."
        />
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {benefits.map((benefit, index) => (
            <AnimatedItem key={benefit.title} delay={index * 0.1}>
              <div className="flex items-start gap-5 p-6 rounded-[20px] bg-surface-card border border-border shadow-sm hover:shadow-md hover:-translate-y-1 transition-all-base h-full">
                <div
                  className={cn(
                    "w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0",
                    "bg-gradient-to-br from-primary-500 to-primary-400 text-white shadow-lg shadow-primary-400/25"
                  )}
                >
                  <Icon name={benefit.icon} className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-text-heading mb-1">{benefit.title}</h3>
                  <p className="text-sm lg:text-base text-text-muted leading-relaxed">
                    {benefit.description}
                  </p>
                </div>
              </div>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
