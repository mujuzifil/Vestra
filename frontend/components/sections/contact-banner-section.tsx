"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { ArrowRight } from "lucide-react";

const prefersReducedMotion =
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export function ContactBannerSection() {
  return (
    <section
      id="contact-banner"
      className="py-20 lg:py-28 bg-gradient-to-br from-secondary-500 to-secondary-600"
      aria-labelledby="contact-banner-heading"
    >
      <Container>
        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto text-center px-6"
        >
          <h2
            id="contact-banner-heading"
            className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white mb-4 tracking-tight"
          >
            Ready to Work with VESTRA®?
          </h2>
          <p className="text-base lg:text-lg text-white/90 mb-8 leading-relaxed">
            Let us understand your requirements and deliver a cleaning solution that supports your
            business goals.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link
              href="/request-quote"
              data-track="contact-banner-quote"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-secondary-600 bg-white hover:bg-white/90 hover:-translate-y-1 transition-all-base group"
            >
              Request a Quote
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
            </Link>
            <Link
              href="/contact"
              data-track="contact-banner-contact"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white border border-white/40 hover:bg-white/10 hover:-translate-y-1 transition-all-base"
            >
              Contact Sales
            </Link>
            <Link
              href="/distributor"
              data-track="contact-banner-distributor"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white border border-white/40 hover:bg-white/10 hover:-translate-y-1 transition-all-base"
            >
              Become a Distributor
            </Link>
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
