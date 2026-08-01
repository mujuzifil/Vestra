"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { PhoneCall, Smartphone, Mail } from "lucide-react";
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
            Need Immediate Assistance?
          </h2>
          <p className="text-base lg:text-lg text-white/75 mb-8">
            Our sales team is ready to discuss your requirements and prepare a quotation.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto bg-white text-text-heading border-transparent hover:bg-white/90" leftIcon={<PhoneCall className="w-4 h-4" />}>
              <Link href="tel:+256707128442">Call Sales</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50" leftIcon={<Smartphone className="w-4 h-4" />}>
              <Link href="https://wa.me/256707128442">WhatsApp</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50" leftIcon={<Mail className="w-4 h-4" />}>
              <Link href="mailto:info@vestradetergents.com">Email</Link>
            </Button>
          </div>
        </div>
      </Container>
    </section>
  );
}
