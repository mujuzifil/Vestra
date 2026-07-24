"use client";

import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { CheckCircle, Clock, ArrowRight, Home, ShoppingBag, Mail, Phone } from "lucide-react";
import { Container } from "@/components/common/container";

export function DistributorSuccessPageClient() {
  const searchParams = useSearchParams();
  const reference = searchParams.get("ref") || "VESTRA-DIST-0000";

  return (
    <main className="min-h-[calc(100vh-88px)] flex items-center justify-center bg-neutral-50 py-12 lg:py-20">
      <Container className="max-w-2xl w-full">
        <div className="bg-white rounded-[24px] border border-neutral-200 shadow-lg p-8 lg:p-12 text-center">
          <div className="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-6">
            <CheckCircle className="w-10 h-10 text-green-600" aria-hidden="true" />
          </div>

          <h1 className="text-2xl lg:text-3xl font-extrabold text-primary-900 mb-3">
            Application Submitted
          </h1>
          <p className="text-muted text-base lg:text-lg mb-8 leading-relaxed">
            Thank you for your interest in becoming a VESTRA distributor. We have received your
            application and our partnership team will review it shortly.
          </p>

          <div className="rounded-[16px] border border-neutral-200 bg-neutral-50 p-6 mb-8 text-left">
            <div className="grid sm:grid-cols-2 gap-6">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">
                  Reference Number
                </p>
                <p className="text-lg font-bold text-primary-900 font-mono">{reference}</p>
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-placeholder mb-1">
                  Expected Review Period
                </p>
                <p className="text-base font-semibold text-primary-900 flex items-center gap-2">
                  <Clock className="w-4 h-4 text-green-600" />
                  5–7 business days
                </p>
              </div>
            </div>
          </div>

          <div className="text-left mb-8">
            <h2 className="text-lg font-bold text-primary-900 mb-4">What happens next?</h2>
            <ul className="space-y-3">
              {[
                "Our team will review your business profile and market fit.",
                "You will receive an email confirmation with your reference number.",
                "A partnership representative may contact you for additional details.",
                "Once approved, you will receive distributor onboarding instructions.",
              ].map((step) => (
                <li key={step} className="flex items-start gap-3 text-body">
                  <span className="w-5 h-5 rounded-full bg-green-500/10 text-green-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                    ✓
                  </span>
                  {step}
                </li>
              ))}
            </ul>
          </div>

          <div className="flex flex-col sm:flex-row gap-3 mb-8">
            <Link
              href="/"
              className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-white border border-neutral-200 hover:bg-neutral-50 transition-colors-base"
            >
              <Home className="w-4 h-4" />
              Return Home
            </Link>
            <Link
              href="/products"
              className="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all-base"
            >
              <ShoppingBag className="w-4 h-4" />
              Continue Shopping
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>

          <div className="pt-6 border-t border-neutral-200 text-sm text-muted">
            <p className="mb-2">Questions about your application?</p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <a
                href="mailto:partnerships@vestra.com"
                className="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 font-medium"
              >
                <Mail className="w-4 h-4" />
                partnerships@vestra.com
              </a>
              <a
                href="tel:+256707128442"
                className="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 font-medium"
              >
                <Phone className="w-4 h-4" />
                +256 707 128 442
              </a>
            </div>
          </div>
        </div>
      </Container>
    </main>
  );
}
