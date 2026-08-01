"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { CTASection } from "@/components/common/cta-section";
import { Newspaper, ArrowRight } from "lucide-react";

export function BlogPageClient() {
  return (
    <main>
      <PageHero
        title="Blog"
        subtitle="Industry insights, product knowledge, and fabric care best practices from the VESTRA team."
        breadcrumb={[{ label: "Blog" }]}
      />

      <section className="py-20 lg:py-28 bg-white" aria-labelledby="blog-coming-soon">
        <Container>
          <div className="max-w-2xl mx-auto text-center">
            <div className="w-20 h-20 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-6">
              <Newspaper className="w-10 h-10 text-green-600" aria-hidden="true" />
            </div>
            <h2 id="blog-coming-soon" className="text-2xl lg:text-3xl font-bold text-primary-900 mb-4">
              Articles Coming Soon
            </h2>
            <p className="text-body text-lg leading-relaxed mb-8">
              We are preparing practical guides on fabric care, institutional laundering,
              distributor best practices, and product application tips. Check back soon,
              or reach out to our sales team for immediate assistance.
            </p>
            <div className="flex flex-col sm:flex-row gap-3 justify-center">
              <Link
                href="/request-quote"
                className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all-base"
              >
                Request a Quote
              </Link>
              <Link
                href="/contact"
                className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
              >
                Contact Sales
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          </div>
        </Container>
      </section>

      <CTASection
        title="Need product or supply advice?"
        description="Our team is ready to help you choose the right solutions for your business, institution, or distribution network."
        buttonText="Contact Sales"
        buttonHref="/contact"
        secondaryButton={{ text: "Become a Distributor", href: "/distributor" }}
        light
      />
    </main>
  );
}
