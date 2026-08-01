"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { Button } from "@/components/ui/button";

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
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto bg-white text-text-heading border-transparent hover:bg-white/90">
              <Link href="/contact">Contact Sales</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/request-quote">Request a Quote</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/distributor">Become a Distributor</Link>
            </Button>
          </div>
        </div>
      </Container>
    </section>
  );
}
