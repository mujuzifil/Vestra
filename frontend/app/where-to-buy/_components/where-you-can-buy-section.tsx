"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";

const channels = [
  { icon: "Store", title: "Authorised Distributors", description: "Purchase through VESTRA® approved distribution partners." },
  { icon: "Warehouse", title: "Wholesale Stores", description: "Bulk supply for resellers and institutional buyers." },
  { icon: "ShoppingCart", title: "Retail Shops", description: "Selected neighbourhood stockists carrying VESTRA® products." },
  { icon: "Droplets", title: "Commercial Laundry Suppliers", description: "Specialised supply channels for professional laundries." },
  { icon: "Building2", title: "Institutional Supply Partners", description: "Dedicated partners for hospitals, schools, and government." },
  { icon: "Landmark", title: "Supermarkets", description: "Premium detergent selections in leading retail chains." },
];

export function WhereYouCanBuySection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="where-you-can-buy-heading">
      <Container>
        <SectionHeader
          id="where-you-can-buy-heading"
          title="Where You Can Buy"
          subtitle="VESTRA® products are available through multiple professional channels."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {channels.map((channel, index) => (
            <ValueCard
              key={channel.title}
              icon={channel.icon}
              title={channel.title}
              description={channel.description}
              index={index}
            />
          ))}
        </div>
      </Container>
    </section>
  );
}
