"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { Icon } from "@/components/common/icon";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

const industries = [
  { icon: "Hotel", title: "Hotels", description: "Housekeeping and linen care at scale." },
  { icon: "Stethoscope", title: "Hospitals", description: "Hygiene-critical laundry solutions." },
  { icon: "School", title: "Schools", description: "Reliable cleaning for institutions." },
  { icon: "Building2", title: "Commercial Laundries", description: "High-volume professional detergents." },
  { icon: "Briefcase", title: "Cleaning Companies", description: "Products built for service teams." },
  { icon: "Landmark", title: "Government", description: "Trusted supply for public institutions." },
  { icon: "HeartHandshake", title: "NGOs", description: "Consistent supply for programmes." },
  { icon: "ShoppingCart", title: "Supermarkets", description: "Retail-ready packaged products." },
  { icon: "TrendingUp", title: "Wholesalers", description: "Competitive distributor pricing." },
  { icon: "Factory", title: "Manufacturers", description: "Integrated cleaning supply partners." },
];

export function IndustriesSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="industries"
      className="py-24 lg:py-36 bg-surface-page"
      aria-labelledby="industries-heading"
    >
      <Container>
        <SectionHeader
          id="industries-heading"
          title="Industries We Serve"
          subtitle="VESTRA® supplies businesses and organisations across Uganda with dependable cleaning and fabric care solutions."
        />

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6">
          {industries.map((industry, index) => (
            <motion.div
              key={industry.title}
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.5, delay: index * 0.05 }}
              className="text-center p-5 lg:p-6 rounded-[20px] bg-white border border-default shadow-sm hover:-translate-y-1.5 hover:shadow-md hover:border-primary-300/50 transition-all-base"
            >
              <div className="w-12 h-12 lg:w-14 lg:h-14 rounded-full bg-primary-50 flex items-center justify-center text-primary-500 mx-auto mb-4">
                <Icon name={industry.icon} className="w-6 h-6 lg:w-7 lg:h-7" aria-hidden="true" />
              </div>
              <h3 className="text-sm lg:text-base font-bold text-text-heading mb-1">{industry.title}</h3>
              <p className="text-xs lg:text-sm text-text-muted leading-relaxed">{industry.description}</p>
            </motion.div>
          ))}
        </div>
      </Container>
    </section>
  );
}
