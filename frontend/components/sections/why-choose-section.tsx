"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

const pillars = [
  {
    number: "01",
    title: "Deep Cleaning",
    description:
      "Powerful cleaning solutions engineered to tackle deep-seated dirt, soils and everyday buildup.",
  },
  {
    number: "02",
    title: "Specialized Fabric Care",
    description:
      "Purpose-built solutions for delicate, premium and specialty fabrics—helping clean while caring for the garment.",
  },
  {
    number: "03",
    title: "Targeted Solutions",
    description:
      "Specialized products designed for specific cleaning challenges, from difficult stains to demanding garment-care applications.",
  },
  {
    number: "04",
    title: "Professional Results",
    description:
      "A complete approach to cleaning, care and finishing for garments that look, feel and perform at their best.",
  },
] as const;

export function WhyChooseSection() {
  const prefersReducedMotion = useReducedMotion();

  return (
    <section
      id="why-choose"
      className="relative overflow-hidden py-16 lg:py-24"
      style={{
        background:
          "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 50%, var(--primary-500) 100%)",
      }}
      aria-labelledby="why-choose-heading"
    >
      <div
        className="pointer-events-none absolute inset-0"
        aria-hidden="true"
        style={{
          background:
            "radial-gradient(circle at 20% 80%, rgba(112,192,80,0.1) 0%, transparent 45%), radial-gradient(circle at 80% 20%, rgba(13,59,102,0.6) 0%, transparent 40%)",
        }}
      />

      <Container className="relative z-10">
        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-80px" }}
          transition={{ duration: 0.6 }}
          className="mx-auto mb-10 max-w-3xl text-center lg:mb-12"
        >
          <h2
            id="why-choose-heading"
            className="mb-4 text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-[clamp(2.25rem,4vw,3.25rem)]"
          >
            Why Choose VESTRA®
          </h2>
          <div className="mx-auto mb-5 h-1 w-20 rounded-full bg-gradient-to-r from-secondary-500 to-primary-300" />
          <p className="text-base leading-relaxed text-white/80 sm:text-lg">
            Different cleaning challenges require different solutions.
          </p>
        </motion.div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
          {pillars.map((pillar, index) => (
            <motion.article
              key={pillar.number}
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-80px" }}
              transition={{ duration: 0.5, delay: prefersReducedMotion ? 0 : index * 0.08 }}
              className="rounded-[20px] border border-white/10 bg-white/5 p-6 text-white backdrop-blur-sm transition-all-base hover:-translate-y-1 hover:border-secondary-500/40 hover:bg-white/10 lg:p-7"
            >
              <p className="mb-4 font-mono text-sm font-bold tracking-[0.18em] text-secondary-500">
                {pillar.number}
              </p>
              <h3 className="mb-3 text-lg font-bold uppercase leading-snug tracking-wide lg:text-xl">
                {pillar.title}
              </h3>
              <p className="text-sm leading-relaxed text-white/75 lg:text-[0.95rem]">
                {pillar.description}
              </p>
            </motion.article>
          ))}
        </div>

        <motion.p
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 16 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-60px" }}
          transition={{ duration: 0.55, delay: prefersReducedMotion ? 0 : 0.2 }}
          className="mt-10 text-center text-lg font-semibold tracking-tight text-white sm:text-xl lg:mt-12 lg:text-2xl"
        >
          Clean deeper. Care smarter. Finish better.
        </motion.p>
      </Container>
    </section>
  );
}
