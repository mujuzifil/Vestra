"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";

const quoteBenefits = [
  { icon: "Tag", title: "Bulk Pricing", description: "Competitive wholesale rates based on your order volume and frequency." },
  { icon: "Factory", title: "Manufacturer Direct", description: "Buy directly from the producer with no middleman margins." },
  { icon: "Scale", title: "Flexible Quantities", description: "Order the volume that fits your operation, large or small." },
  { icon: "Headphones", title: "Professional Advice", description: "Get product recommendations from our sales specialists." },
  { icon: "Truck", title: "Reliable Supply", description: "Consistent production and dependable delivery scheduling." },
  { icon: "Clock", title: "Fast Response", description: "Receive a tailored quotation within 24–48 business hours." },
];

export function WhyQuoteSection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="why-quote-heading">
      <Container>
        <SectionHeader
          id="why-quote-heading"
          title="Why Request a Quote"
          subtitle="Get pricing and terms designed around your business needs."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
  );
}
