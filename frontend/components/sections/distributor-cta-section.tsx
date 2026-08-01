"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, CheckCircle2 } from "lucide-react";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";

const benefits = [
  "Competitive distributor pricing and margins",
  "Marketing and sales support materials",
  "Reliable supply and delivery schedules",
  "Exclusive territory opportunities",
  "Product training and technical guidance",
];

const prefersReducedMotion =
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export function DistributorCtaSection() {
  return (
    <section
      id="distributor"
      className="py-24 lg:py-36 bg-surface-page"
      aria-labelledby="distributor-heading"
    >
      <Container>
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: -40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7 }}
            className="relative order-2 lg:order-1"
          >
            <div className="relative aspect-square max-w-md mx-auto rounded-[24px] overflow-hidden bg-primary-50 shadow-xl">
              <Image
                src="/assets/images/products/heavy-duty-detergent.png"
                alt="VESTRA product packaging for distributor partners"
                fill
                sizes="(max-width: 1024px) 100vw, 40vw"
                className="object-contain p-6 lg:p-10"
              />
            </div>
          </motion.div>

          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: 40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.1 }}
            className="order-1 lg:order-2"
          >
            <SectionHeader
              id="distributor-heading"
              title="Become a VESTRA® Distributor"
              subtitle="Partner with a growing Ugandan manufacturer. Join our distributor network and build a sustainable business in professional cleaning and fabric care."
              centered={false}
              className="mb-8"
            />

            <ul className="space-y-4 mb-8">
              {benefits.map((benefit) => (
                <li key={benefit} className="flex items-start gap-3">
                  <CheckCircle2
                    className="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5"
                    aria-hidden="true"
                  />
                  <span className="text-body leading-relaxed">{benefit}</span>
                </li>
              ))}
            </ul>

            <Link
              href="/distributor"
              data-track="distributor-section-cta"
              className="inline-flex items-center gap-2 px-8 py-4 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-1 transition-transform-base group"
            >
              Apply Now
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
            </Link>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
