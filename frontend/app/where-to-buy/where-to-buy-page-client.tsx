"use client";

import { useEffect, useState } from "react";
import { Loader2 } from "lucide-react";
import { Container } from "@/components/common/container";
import { FAQAccordion } from "@/components/common/faq-accordion";
import { useContactInfo, useSettings } from "@/hooks/use-settings";
import { getSettingValue } from "@/lib/api/settings";
import { getDistributorNetworkStats } from "@/lib/api/public-distributors";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";
import { WhereToBuyHero } from "./_components/where-to-buy-hero";
import { NetworkStatsSection } from "./_components/network-stats-section";
import { WhereYouCanBuySection } from "./_components/where-you-can-buy-section";
import { CoverageSection } from "./_components/coverage-section";
import { DirectorySection } from "./_components/directory-section";
import { NeedHelpSection } from "./_components/need-help-section";
import { RelatedResourcesSection } from "./_components/related-resources-section";
import { FinalCTASection } from "./_components/final-cta-section";

const whereToBuyFaqs = [
  {
    question: "Where can I buy VESTRA® products?",
    answer: "VESTRA® products are available through authorised distributors, wholesale stores, retail shops, commercial laundry suppliers, institutional supply partners, and selected supermarkets across Uganda.",
  },
  {
    question: "Do you supply nationwide?",
    answer: "Yes. Our distribution network continues to expand. If your district is not yet covered, our sales team can arrange direct supply or connect you with the nearest distributor.",
  },
  {
    question: "Can I order directly from VESTRA®?",
    answer: "Institutions, hotels, laundries, and large organisations can request a direct-supply quotation. Retail customers should use an authorised distributor or retail partner.",
  },
  {
    question: "How long does delivery take?",
    answer: "Delivery timelines depend on location and order size. Our sales team confirms lead times when preparing your quotation.",
  },
  {
    question: "Do you support commercial customers?",
    answer: "Absolutely. We provide bulk pricing, scheduled deliveries, and account management for commercial and institutional customers.",
  },
];

export function WhereToBuyPageClient() {
  const { contactInfo, isLoading: contactLoading } = useContactInfo();
  const { data: settings } = useSettings();
  const [apiStats, setApiStats] = useState({ active_distributors: 0, branches: 0, districts_served: 0, commercial_customers: 0 });
  const [statsLoading, setStatsLoading] = useState(true);

  useEffect(() => {
    getDistributorNetworkStats()
      .then((data) => setApiStats(data))
      .catch(() => setApiStats({ active_distributors: 0, branches: 0, districts_served: 0, commercial_customers: 0 }))
      .finally(() => setStatsLoading(false));
  }, []);

  const phone = contactInfo?.phone || "+256 707 128 442";
  const email = contactInfo?.email || "info@vestradetergents.com";
  const whatsapp = contactInfo?.whatsapp || "+256 707 128 442";

  const stats = {
    districts: statsLoading
      ? Number(getSettingValue(settings || [], "network_districts_served", "0"))
      : Math.max(apiStats.districts_served, Number(getSettingValue(settings || [], "network_districts_served", "0"))),
    partners: statsLoading
      ? Number(getSettingValue(settings || [], "network_authorised_partners", "0"))
      : Math.max(apiStats.active_distributors, Number(getSettingValue(settings || [], "network_authorised_partners", "0"))),
    customers: statsLoading
      ? Number(getSettingValue(settings || [], "network_commercial_customers", "0"))
      : Math.max(apiStats.commercial_customers, Number(getSettingValue(settings || [], "network_commercial_customers", "0"))),
    networkLabel: getSettingValue(settings || [], "network_growing_network_label", "Growing Network"),
  };

  if (contactLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "Where to Buy", url: "https://vestradetergents.com/where-to-buy" },
        ])}
      />
      <main>
        <WhereToBuyHero />
        <NetworkStatsSection stats={stats} />
        <WhereYouCanBuySection />
        <CoverageSection />
        <DirectorySection contactPhone={phone} contactEmail={email} />
        <NeedHelpSection contactPhone={phone} contactEmail={email} whatsapp={whatsapp} />

        {/* FAQ */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="where-to-buy-faq-heading">
          <Container>
            <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
              <div>
                <h2
                  id="where-to-buy-faq-heading"
                  className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold tracking-tight mb-4"
                >
                  Frequently Asked Questions
                </h2>
                <p className="text-base lg:text-lg text-text-muted leading-relaxed">
                  Common questions about finding and buying VESTRA® products.
                </p>
              </div>
              <FAQAccordion items={whereToBuyFaqs} />
            </div>
          </Container>
        </section>

        <RelatedResourcesSection />
        <FinalCTASection />
      </main>
    </>
  );
}
