"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { CheckCircle2 } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

const capabilities = [
  "Modern manufacturing processes built for scale",
  "Rigorous quality assurance at every stage",
  "Consistent product batches and supply reliability",
  "Professional workforce and technical expertise",
];

export function ManufacturingSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="manufacturing"
      className="py-24 lg:py-36 bg-white overflow-x-clip"
      aria-labelledby="manufacturing-heading"
    >
      <Container>
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7 }}
            className="min-w-0"
          >
            <SectionHeader
              id="manufacturing-heading"
              title="Manufacturing Excellence"
              subtitle="We combine advanced chemistry with local manufacturing expertise to deliver cleaning solutions that meet professional standards."
              centered={false}
              className="mb-8"
            />

            <ul className="space-y-4 mb-8">
              {capabilities.map((capability) => (
                <li key={capability} className="flex items-start gap-3">
                  <CheckCircle2 className="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span className="text-text-body leading-relaxed">{capability}</span>
                </li>
              ))}
            </ul>

            <p className="text-text-body leading-relaxed">
              From raw material sourcing to final packaging, every step is managed with precision to
              ensure our partners receive products they can rely on.
            </p>
          </motion.div>

          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.1 }}
            className="relative min-w-0"
          >
            <div className="relative aspect-[4/3] rounded-[24px] overflow-hidden bg-primary-50 shadow-xl">
              <Image
                src="/assets/images/hero/home-page-image.webp"
                alt="VESTRA detergent product range representing local manufacturing quality"
                fill
                sizes="(max-width: 1024px) 100vw, 50vw"
                className="object-contain p-6 lg:p-10"
              />
            </div>
            <div className="absolute bottom-4 left-4 sm:bottom-6 sm:left-6 lg:-bottom-8 lg:-left-8 bg-primary-900 text-white p-4 sm:p-5 lg:p-6 rounded-[20px] shadow-xl max-w-[180px] sm:max-w-[200px]">
              <p className="text-3xl lg:text-4xl font-black text-secondary-500 mb-1">100%</p>
              <p className="text-sm font-medium text-white/80">Quality-tested batches</p>
            </div>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
