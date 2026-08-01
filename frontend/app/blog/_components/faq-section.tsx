"use client";

import { Container } from "@/components/common/container";
import { FAQAccordion } from "@/components/common/faq-accordion";
import type { FaqItem } from "@/types";

const blogFaqs: FaqItem[] = [
  {
    question: "How often are articles published?",
    answer:
      "We publish new articles regularly, focusing on seasonal fabric care topics, product application guides, and industry best practices.",
  },
  {
    question: "Who writes the articles?",
    answer:
      "Articles are written by the VESTRA® product and commercial team, experienced distributors, and invited industry specialists.",
  },
  {
    question: "Can businesses contribute?",
    answer:
      "Yes. Distributors, commercial laundries, and institutional customers can contact our team to share case studies or topic suggestions.",
  },
  {
    question: "Can I request a topic?",
    answer:
      "Absolutely. Send your request through our Contact page and our content team will consider it for an upcoming article.",
  },
];

export function BlogFaqSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="blog-faq-heading">
      <Container>
        <div className="grid lg:grid-cols-[0.8fr_1.2fr] gap-12 lg:gap-16">
          <div>
            <h2
              id="blog-faq-heading"
              className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold tracking-tight mb-4"
            >
              Frequently Asked Questions
            </h2>
            <p className="text-base lg:text-lg text-text-muted leading-relaxed">
              Common questions about the VESTRA® Knowledge Centre.
            </p>
          </div>
          <FAQAccordion items={blogFaqs} />
        </div>
      </Container>
    </section>
  );
}
