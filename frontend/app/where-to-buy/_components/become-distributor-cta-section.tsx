"use client";

import Link from "next/link";
import { ArrowRight, Briefcase } from "lucide-react";
import { Container } from "@/components/common/container";
import { Button } from "@/components/ui/button";

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
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto bg-white text-text-heading border-transparent hover:bg-white/90" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />}>
              <Link href="/distributor">Become a Distributor</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/request-quote">Request Information</Link>
            </Button>
          </div>
        </div>
      </Container>
    </section>
  );
}
