"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { ArrowRight } from "lucide-react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { Button } from "@/components/ui/button";

export function ContactBannerSection() {
  const prefersReducedMotion = useReducedMotion();
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
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto bg-white text-text-heading border-transparent hover:bg-white/90" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
              <Link href="/request-quote" data-track="contact-banner-quote">Request a Quote</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/contact" data-track="contact-banner-contact">Contact Sales</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/distributor" data-track="contact-banner-distributor">Become a Distributor</Link>
            </Button>
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
