"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";

const supportChannels = [
  {
    icon: "PhoneCall",
    title: "Call Sales",
    value: "+256 707 128 442",
    href: "tel:+256707128442",
    description: "Speak directly with a sales specialist.",
  },
  {
    icon: "Smartphone",
    title: "WhatsApp",
    value: "+256 707 128 442",
    href: "https://wa.me/256707128442",
    description: "Message us for quick responses during business hours.",
  },
  {
    icon: "Mail",
    title: "Email",
    value: "vestradetergent@gmail.com",
    href: "mailto:vestradetergent@gmail.com",
    description: "Send detailed requirements and attachments.",
  },
  {
    icon: "Clock",
    title: "Business Hours",
    value: "Mon – Fri, 8:00 – 17:00",
    href: "#",
    description: "EAT. We aim to respond within 24 hours.",
  },
];

export function CustomerSupportSection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="support-heading">
      <Container>
        <SectionHeader
          id="support-heading"
          title="Customer Support"
          subtitle="Need help with your quotation? Reach out through any of these channels."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {supportChannels.map((channel, index) => (
            <AnimatedItem key={channel.title} delay={index * 0.1}>
              <a
                href={channel.href}
                className="block p-6 rounded-[20px] bg-surface-card border border-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base h-full"
              >
                <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-secondary-500 to-secondary-600 text-white flex items-center justify-center mb-4 shadow-lg shadow-secondary-500/25">
                  <Icon name={channel.icon} className="w-6 h-6" />
                </div>
                <h3 className="text-lg font-bold text-text-heading mb-1">{channel.title}</h3>
                <p className="text-base font-semibold text-primary-900 mb-2">{channel.value}</p>
                <p className="text-sm text-text-muted">{channel.description}</p>
              </a>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
