"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { Quote } from "lucide-react";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

/**
 * Placeholder testimonials.
 * Replace these with real customer quotes once testimonials have been collected.
 */
const testimonials = [
  {
    quote:
      "VESTRA has consistently delivered quality detergent products that meet the demands of our commercial laundry operation.",
    author: "Operations Manager",
    company: "Commercial Laundry Partner",
  },
  {
    quote:
      "Their team understands institutional supply. We trust VESTRA for reliable delivery and professional support.",
    author: "Procurement Lead",
    company: "Hospitality Group",
  },
  {
    quote:
      "Switching to VESTRA improved fabric care results across our facility. A strong local manufacturing partner.",
    author: "Housekeeping Director",
    company: "Hotel & Resort",
  },
];

export function TestimonialsSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="testimonials"
      className="py-24 lg:py-36 bg-surface-page"
      aria-labelledby="testimonials-heading"
    >
      <Container>
        <SectionHeader
          id="testimonials-heading"
          title="What Our Partners Say"
          subtitle="Feedback from businesses and institutions that rely on VESTRA® products and support."
        />

        <div className="grid md:grid-cols-3 gap-6 lg:gap-8">
          {testimonials.map((testimonial, index) => (
            <motion.article
              key={index}
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              className="relative p-6 lg:p-8 rounded-[20px] bg-white border border-default shadow-sm"
            >
              <Quote
                className="absolute top-6 left-6 w-8 h-8 text-secondary-500/20 fill-secondary-500/10"
                aria-hidden="true"
              />
              <p className="relative z-10 text-text-body leading-relaxed mb-6 pt-8">
                &ldquo;{testimonial.quote}&rdquo;
              </p>
              <div>
                <p className="font-semibold text-text-heading text-sm">{testimonial.author}</p>
                <p className="text-xs text-text-muted">{testimonial.company}</p>
              </div>
            </motion.article>
          ))}
        </div>

        <p className="sr-only">
          These testimonials are placeholder content. Replace with verified customer quotes before
          production publication.
        </p>
      </Container>
    </section>
  );
}
