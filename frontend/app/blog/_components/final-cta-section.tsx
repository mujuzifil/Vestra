"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";

export function FinalCTASection() {
  return (
    <section
      className="py-20 lg:py-28"
      style={{
        background: "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 100%)",
      }}
    >
      <Container>
        <div className="max-w-3xl mx-auto text-center px-6">
          <h2 className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4 tracking-tight">
            Need Commercial Cleaning Advice?
          </h2>
          <p className="text-base lg:text-lg text-white/75 mb-8">
            Speak with our sales team, request a tailored quotation, or explore partnership opportunities.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              href="/contact"
              className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white shadow-lg hover:-translate-y-1 transition-transform-base"
            >
              Contact Sales
            </Link>
            <Link
              href="/request-quote"
              className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              Request a Quote
            </Link>
            <Link
              href="/distributor"
              className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              Become a Distributor
            </Link>
          </div>
        </div>
      </Container>
    </section>
  );
}
