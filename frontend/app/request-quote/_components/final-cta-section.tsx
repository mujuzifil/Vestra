"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { PhoneCall, Smartphone, Mail } from "lucide-react";

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
            <Link
              href="tel:+256707128442"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white shadow-lg hover:-translate-y-1 transition-transform-base"
            >
              <PhoneCall className="w-4 h-4" />
              Call Sales
            </Link>
            <Link
              href="https://wa.me/256707128442"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              <Smartphone className="w-4 h-4" />
              WhatsApp
            </Link>
            <Link
              href="mailto:vestradetergent@gmail.com"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              <Mail className="w-4 h-4" />
              Email
            </Link>
          </div>
        </div>
      </Container>
    </section>
  );
}
