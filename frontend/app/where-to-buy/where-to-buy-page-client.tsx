"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { CTASection } from "@/components/common/cta-section";
import { MapPin, Phone, Mail, Store, Truck, Users } from "lucide-react";
import { useContactInfo } from "@/hooks/use-settings";

const channels = [
  {
    icon: Store,
    title: "Authorised Distributors",
    description:
      "Access VESTRA products through our network of approved distributors across Uganda and the East African region.",
  },
  {
    icon: Truck,
    title: "Direct Supply",
    description:
      "Institutions, hotels, laundries, and large organisations can order directly with scheduled delivery and volume pricing.",
  },
  {
    icon: Users,
    title: "Become a Distributor",
    description:
      "Join our growing distribution network and gain access to protected territories, marketing support, and training.",
  },
];

export function WhereToBuyPageClient() {
  const { contactInfo } = useContactInfo();

  const phone = contactInfo?.phone || "+256 707 128 442";
  const email = contactInfo?.email || "vestradetergent@gmail.com";
  const location = contactInfo?.location || "Kampala, Uganda";

  return (
    <main>
      <PageHero
        title="Where to Buy"
        subtitle="Find VESTRA products through authorised distributors, direct supply, or by becoming a partner."
        breadcrumb={[{ label: "Where to Buy" }]}
      />

      <section className="py-20 lg:py-28 bg-white" aria-labelledby="channels-heading">
        <Container>
          <SectionHeader
            id="channels-heading"
            title="How to Access VESTRA Products"
            subtitle="Choose the option that best suits your business or institution."
          />
          <div className="grid md:grid-cols-3 gap-6 lg:gap-8">
            {channels.map((channel) => (
              <div
                key={channel.title}
                className="bg-surface-page rounded-[20px] p-6 lg:p-8 border border-default hover:-translate-y-2 hover:shadow-lg transition-all-base"
              >
                <div className="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center mb-5">
                  <channel.icon className="w-6 h-6 text-green-600" aria-hidden="true" />
                </div>
                <h3 className="text-lg font-bold text-primary-900 mb-3">{channel.title}</h3>
                <p className="text-body leading-relaxed">{channel.description}</p>
              </div>
            ))}
          </div>
        </Container>
      </section>

      <section className="py-20 lg:py-28 bg-surface-page" aria-labelledby="contact-heading">
        <Container>
          <div className="max-w-4xl mx-auto bg-white rounded-[24px] border border-default shadow-lg overflow-hidden">
            <div className="grid md:grid-cols-2">
              <div className="p-8 lg:p-10 bg-primary-900 text-white">
                <h2 id="contact-heading" className="text-2xl font-bold mb-6">
                  Contact Sales
                </h2>
                <p className="text-white/80 mb-8 leading-relaxed">
                  Speak with our sales team about stockists, direct supply, distributor partnerships, or institutional orders.
                </p>
                <ul className="space-y-5">
                  <li className="flex items-start gap-4">
                    <MapPin className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" />
                    <span className="text-white/90">{location}</span>
                  </li>
                  <li className="flex items-start gap-4">
                    <Phone className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" />
                    <a href={`tel:${phone.replace(/\s/g, "")}`} className="text-white/90 hover:text-secondary-400 transition-colors-base">
                      {phone}
                    </a>
                  </li>
                  <li className="flex items-start gap-4">
                    <Mail className="w-5 h-5 text-secondary-500 mt-0.5 flex-shrink-0" />
                    <a href={`mailto:${email}`} className="text-white/90 hover:text-secondary-400 transition-colors-base">
                      {email}
                    </a>
                  </li>
                </ul>
              </div>
              <div className="p-8 lg:p-10 flex flex-col justify-center gap-4">
                <Link
                  href="/request-quote"
                  className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-0.5 transition-all-base"
                >
                  Request a Quote
                </Link>
                <Link
                  href="/distributor"
                  className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
                >
                  Become a Distributor
                </Link>
                <Link
                  href="/contact"
                  className="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-semibold text-primary-900 bg-surface-card border border-default hover:bg-surface-page transition-colors-base"
                >
                  Contact Sales
                </Link>
              </div>
            </div>
          </div>
        </Container>
      </section>

      <CTASection
        title="Looking for a local distributor?"
        description="Our team can connect you with the nearest authorised stockist or arrange direct delivery for your organisation."
        buttonText="Request a Quote"
        buttonHref="/request-quote"
        secondaryButton={{ text: "Contact Sales", href: "/contact" }}
      />
    </main>
  );
}
