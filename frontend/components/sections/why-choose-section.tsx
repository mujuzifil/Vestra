"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

const whyChooseFeatures = [
  { icon: "Award", title: "Premium Quality", description: "Manufactured to professional standards with rigorous quality control." },
  { icon: "Factory", title: "Manufacturer Direct", description: "Source directly from the manufacturer for consistency and value." },
  { icon: "Truck", title: "Reliable Supply", description: "Dependable production and delivery schedules for your business." },
  { icon: "ShoppingCart", title: "Bulk Orders", description: "Flexible commercial volumes tailored to institutional demand." },
  { icon: "Globe", title: "Nationwide Distribution", description: "Supplying businesses and distributors across Uganda." },
  { icon: "HeartHandshake", title: "Professional Support", description: "Dedicated sales and technical support for every partner." },
];

export function WhyChooseSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="why-choose"
      className="relative py-24 lg:py-36 overflow-hidden"
      style={{
        background:
          "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 50%, var(--primary-500) 100%)",
      }}
      aria-labelledby="why-choose-heading"
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            "radial-gradient(circle at 20% 80%, rgba(112,192,80,0.1) 0%, transparent 45%), radial-gradient(circle at 80% 20%, rgba(13,59,102,0.6) 0%, transparent 40%)",
        }}
      />

      <Container className="relative z-10">
        <motion.h2
          id="why-choose-heading"
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white text-center mb-4 tracking-tight"
        >
          Why Choose VESTRA®
        </motion.h2>
        <div className="w-20 h-1 bg-gradient-to-r from-secondary-500 to-primary-300 rounded-full mx-auto mb-16" />

        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {whyChooseFeatures.map((feature, index) => (
            <motion.div
              key={feature.title}
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.6, delay: index * 0.08 }}
              className="text-center text-white p-8 lg:p-9 rounded-[20px] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 hover:-translate-y-2 hover:border-secondary-500/40 hover:shadow-xl transition-all-base"
            >
              <div className="w-16 h-16 lg:w-[72px] lg:h-[72px] rounded-full border-2 border-white/25 flex items-center justify-center mx-auto mb-5 text-secondary-500 group-hover:border-secondary-500 group-hover:bg-secondary-500/10 transition-colors-base">
                <Icon name={feature.icon} className="w-7 h-7 lg:w-8 lg:h-8" aria-hidden="true" />
              </div>
              <h3 className="text-base lg:text-lg font-semibold mb-2 leading-snug">{feature.title}</h3>
              <p className="text-sm lg:text-base text-white/70 leading-relaxed">{feature.description}</p>
            </motion.div>
          ))}
        </div>
      </Container>
    </section>
  );
}
