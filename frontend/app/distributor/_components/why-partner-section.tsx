"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";

const partnerReasons = [
  {
    icon: "Award",
    title: "Trusted Brand",
    description: "Associate with a professionally recognised Ugandan detergent manufacturer.",
  },
  {
    icon: "Scale",
    title: "Competitive Margins",
    description: "Benefit from manufacturer-direct pricing and healthy reseller margins.",
  },
  {
    icon: "TrendingUp",
    title: "Growing Demand",
    description: "Meet rising institutional, commercial, and retail demand for quality cleaning products.",
  },
  {
    icon: "Truck",
    title: "Reliable Supply",
    description: "Count on consistent production and dependable delivery schedules.",
  },
  {
    icon: "FileText",
    title: "Marketing Support",
    description: "Access branded materials, product literature, and co-promotional resources.",
  },
  {
    icon: "HeartHandshake",
    title: "Sales Assistance",
    description: "Work alongside a dedicated partnership team to grow your territory.",
  },
];

export function WhyPartnerSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="why-partner-heading">
      <Container>
        <SectionHeader
          id="why-partner-heading"
          title="Why Partner With VESTRA®"
          subtitle="We equip authorised distributors with the products, tools, and support needed to succeed."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {partnerReasons.map((item, index) => (
            <ValueCard
              key={item.title}
              icon={item.icon}
              title={item.title}
              description={item.description}
              index={index}
            />
          ))}
        </div>
      </Container>
    </section>
  );
}
