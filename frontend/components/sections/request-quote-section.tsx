"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ArrowRight, Hotel, School, Factory, HeartHandshake, Briefcase, Stethoscope } from "lucide-react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { Button } from "@/components/ui/button";

const audiences = [
  { icon: Hotel, label: "Hotels" },
  { icon: Stethoscope, label: "Hospitals" },
  { icon: School, label: "Schools" },
  { icon: HeartHandshake, label: "NGOs" },
  { icon: Briefcase, label: "Corporate Clients" },
  { icon: Factory, label: "Manufacturers" },
];

export function RequestQuoteSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="request-quote"
      className="py-24 lg:py-36 bg-primary-900 relative overflow-hidden"
      aria-labelledby="request-quote-heading"
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            "radial-gradient(circle at 30% 70%, rgba(112,192,80,0.12) 0%, transparent 45%), radial-gradient(circle at 80% 20%, rgba(255,255,255,0.04) 0%, transparent 40%)",
        }}
      />

      <Container className="relative z-10">
        <SectionHeader
          id="request-quote-heading"
          title="Request a Quote"
          subtitle="Tell us what your business or institution needs. Our sales team will prepare a tailored quotation and respond within 24–48 business hours."
          light
        />

        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.6 }}
          className="flex flex-wrap justify-center gap-3 mb-10"
        >
          {audiences.map((audience) => (
            <div
              key={audience.label}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/10 text-white/90 text-sm"
            >
              <audience.icon className="w-4 h-4 text-secondary-500" aria-hidden="true" />
              {audience.label}
            </div>
          ))}
        </motion.div>

        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.6, delay: 0.1 }}
          className="text-center"
        >
          <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto group" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
              <Link href="/request-quote" data-track="quote-section-cta">Request a Quote</Link>
            </Button>
        </motion.div>
      </Container>
    </section>
  );
}
