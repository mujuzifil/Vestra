"use client";

import { Container } from "@/components/common/container";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";

const stats = [
  {
    icon: "Users",
    value: "Growing Network",
    label: "Authorised partners across Uganda",
  },
  {
    icon: "Building2",
    value: "Business Partnerships",
    label: "Hotels, hospitals, schools & more",
  },
  {
    icon: "MapPin",
    value: "Nationwide Coverage",
    label: "Distribution in all major regions",
  },
  {
    icon: "Clock",
    value: "Fast Response",
    label: "Review within 5–7 business days",
  },
];

export function DistributorStatsSection() {
  return (
    <section className="py-20 lg:py-28 bg-primary-900" aria-labelledby="stats-heading">
      <Container>
        <div className="text-center mb-12 lg:mb-16">
          <h2 id="stats-heading" className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
            Partnership at a Glance
          </h2>
          <p className="mt-4 text-base lg:text-lg text-white/70 max-w-2xl mx-auto">
            Key indicators of the VESTRA® distributor network. Live metrics will replace these placeholders in a future release.
          </p>
        </div>
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {stats.map((stat, index) => (
            <AnimatedItem key={stat.label} delay={index * 0.1}>
              <div className="p-6 rounded-[20px] bg-white/5 border border-white/10 text-center backdrop-blur-sm hover:bg-white/10 transition-colors-base h-full">
                <div className="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center text-white mx-auto mb-4">
                  <Icon name={stat.icon} className="w-7 h-7" />
                </div>
                <p className="text-lg font-bold text-white mb-1">{stat.value}</p>
                <p className="text-sm text-white/60">{stat.label}</p>
              </div>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
