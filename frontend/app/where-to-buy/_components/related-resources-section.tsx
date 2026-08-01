"use client";

import Link from "next/link";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { AnimatedItem } from "@/components/common/animated-section";
import { Icon } from "@/components/common/icon";
import { ArrowRight } from "lucide-react";

const resources = [
  { icon: "Package", title: "Products", description: "Browse the full VESTRA® product range.", href: "/products" },
  { icon: "FileText", title: "Request a Quote", description: "Get a tailored commercial quotation.", href: "/request-quote" },
  { icon: "Newspaper", title: "Blog", description: "Read news, tips, and business insights.", href: "/blog" },
  { icon: "MessageCircle", title: "Contact", description: "Speak directly with our team.", href: "/contact" },
];

export function RelatedResourcesSection() {
  return (
    <section className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="resources-heading">
      <Container>
        <SectionHeader
          id="resources-heading"
          title="Related Resources"
          subtitle="Explore more ways to connect with VESTRA®."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {resources.map((resource, index) => (
            <AnimatedItem key={resource.title} delay={index * 0.1}>
              <Link
                href={resource.href}
                className="group block p-6 rounded-[20px] bg-surface-card border border-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all-base h-full"
              >
                <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-400 text-white flex items-center justify-center mb-4 shadow-lg shadow-primary-400/25">
                  <Icon name={resource.icon} className="w-6 h-6" />
                </div>
                <h3 className="text-lg font-bold text-text-heading mb-2">{resource.title}</h3>
                <p className="text-sm text-text-muted mb-4">{resource.description}</p>
                <span className="inline-flex items-center gap-1 text-sm font-semibold text-secondary-600 group-hover:text-secondary-700">
                  Learn more
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />
                </span>
              </Link>
            </AnimatedItem>
          ))}
        </div>
      </Container>
    </section>
  );
}
