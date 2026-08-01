"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";

interface NetworkStatsSectionProps {
  stats: {
    districts: number;
    partners: number;
    customers: number;
    networkLabel: string;
  };
}

export function NetworkStatsSection({ stats }: NetworkStatsSectionProps) {
  const cards = [
    { icon: "MapPin", value: stats.districts, label: "Districts Served" },
    { icon: "Store", value: stats.partners, label: "Authorised Partners" },
    { icon: "Building2", value: stats.customers, label: "Commercial Customers" },
    { icon: "TrendingUp", value: stats.networkLabel, label: "Network Status" },
  ];

  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="network-stats-heading">
      <Container>
        <SectionHeader
          id="network-stats-heading"
          title="Distribution Network"
          subtitle="A growing footprint across Uganda."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {cards.map((card, index) => (
            <AnimatedItem key={card.label} delay={index * 0.1}>
              <div className="p-6 rounded-[20px] bg-surface-card border border-border shadow-sm text-center hover:-translate-y-1 hover:shadow-md transition-all-base h-full">
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-400 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-400/25">
                  <Icon name={card.icon} className="w-7 h-7" />
                </div>
                <p className="text-2xl lg:text-3xl font-extrabold text-text-heading mb-1">
                  {typeof card.value === "number" ? card.value.toLocaleString() : card.value}
                </p>
                <p className="text-sm text-text-muted">{card.label}</p>
              </div>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
