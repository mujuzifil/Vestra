"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";

interface NeedHelpSectionProps {
  contactPhone: string;
  contactEmail: string;
  whatsapp: string;
}

export function NeedHelpSection({ contactPhone, contactEmail, whatsapp }: NeedHelpSectionProps) {
  const channels = [
    {
      icon: "PhoneCall",
      title: "Call Sales",
      value: contactPhone,
      href: `tel:${contactPhone.replace(/\s/g, "")}`,
    },
    {
      icon: "Smartphone",
      title: "WhatsApp",
      value: whatsapp,
      href: `https://wa.me/${whatsapp.replace(/\s/g, "").replace(/^\+/, "")}`,
    },
    {
      icon: "Mail",
      title: "Email Sales",
      value: contactEmail,
      href: `mailto:${contactEmail}`,
    },
  ];

  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="need-help-heading">
      <Container>
        <SectionHeader
          id="need-help-heading"
          title="Need Help Finding a Distributor?"
          subtitle="If a distributor cannot be found, VESTRA® Sales will connect you with the nearest authorised partner."
        />
        <div className="grid sm:grid-cols-3 gap-6">
          {channels.map((channel, index) => (
            <AnimatedItem key={channel.title} delay={index * 0.1}>
              <a
                href={channel.href}
                className="block p-6 rounded-[20px] bg-surface-card border border-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base text-center h-full"
              >
                <div className="w-14 h-14 rounded-full bg-gradient-to-br from-secondary-500 to-secondary-600 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-secondary-500/25">
                  <Icon name={channel.icon} className="w-7 h-7" />
                </div>
                <h3 className="text-lg font-bold text-text-heading mb-1">{channel.title}</h3>
                <p className="text-base font-semibold text-primary-900">{channel.value}</p>
              </a>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
