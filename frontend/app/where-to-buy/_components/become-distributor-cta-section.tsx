"use client";

import Link from "next/link";
import { ArrowRight, Briefcase } from "lucide-react";
import { Container } from "@/components/common/container";

export function BecomeDistributorCTASection() {
  return (
    <section className="py-20 lg:py-28 bg-primary-900" aria-labelledby="become-distributor-heading">
      <Container>
        <div className="max-w-3xl mx-auto text-center px-6">
          <div className="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-6">
            <Briefcase className="w-8 h-8 text-white" />
          </div>
          <h2
            id="become-distributor-heading"
            className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4 tracking-tight"
          >
            Become a VESTRA® Distributor
          </h2>
          <p className="text-base lg:text-lg text-white/75 mb-8">
            Join our growing network of authorised partners. Gain access to protected territories, competitive margins, marketing support, and dedicated training.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              href="/distributor"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white shadow-lg hover:-translate-y-1 transition-transform-base group"
            >
              Become a Distributor
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />
            </Link>
            <Link
              href="/request-quote"
              className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              Request Information
            </Link>
          </div>
        </div>
      </Container>
    </section>
  );
}
