"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";

const applicantTypes = [
  {
    icon: "Warehouse",
    title: "Wholesalers",
    description: "Move large volumes through established wholesale networks.",
  },
  {
    icon: "Store",
    title: "Retail Chains",
    description: "Place VESTRA® products in supermarkets and retail outlets.",
  },
  {
    icon: "Globe",
    title: "Regional Distributors",
    description: "Cover districts and regions beyond the central corridor.",
  },
  {
    icon: "Building2",
    title: "Supermarkets",
    description: "Stock premium detergents for household and commercial buyers.",
  },
  {
    icon: "Droplets",
    title: "Cleaning Suppliers",
    description: "Bundle VESTRA® products with professional cleaning services.",
  },
  {
    icon: "Truck",
    title: "Commercial Supply Companies",
    description: "Serve hotels, hospitals, schools, and institutions.",
  },
  {
    icon: "Users",
    title: "Entrepreneurs",
    description: "Start or scale a distribution business with VESTRA® backing.",
  },
  {
    icon: "Landmark",
    title: "Institutional Suppliers",
    description: "Provide reliable cleaning products to government and NGOs.",
  },
];

export function WhoCanApplySection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="who-can-apply-heading">
      <Container>
        <SectionHeader
          id="who-can-apply-heading"
          title="Who Can Apply"
          subtitle="We partner with capable businesses and motivated individuals ready to represent VESTRA®."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {applicantTypes.map((item, index) => (
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
