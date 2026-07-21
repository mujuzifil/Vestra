"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";
const whyChooseFeatures = [
  { icon: "Droplets", title: "Deep Cleaning", description: "Advanced formulas penetrate fibers for thorough cleaning." },
  { icon: "Shield", title: "Fabric Safe", description: "Protects colors and delicate fabrics during every wash." },
  { icon: "Leaf", title: "Eco Conscious", description: "Formulated with environmental responsibility in mind." },
  { icon: "Sparkles", title: "Long Lasting Freshness", description: "Keeps fabrics smelling fresh for longer." },
  { icon: "FlaskConical", title: "Scientifically Tested", description: "Proven performance through rigorous testing." },
  { icon: "Truck", title: "Professional Grade", description: "Trusted by professionals and commercial laundries." },
];

export function WhyChooseSection() {
  return (
    <section
      id="why-choose"
      className="relative py-24 lg:py-36 overflow-hidden"
      style={{
        background: "linear-gradient(135deg, #050d18 0%, #0d1f33 50%, #0d3b66 100%)",
      }}
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
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white text-center mb-4 tracking-tight"
        >
          Why Choose VESTRA
        </motion.h2>
        <div className="w-20 h-1 bg-gradient-to-r from-green-500 to-[#7db8ec] rounded-full mx-auto mb-16" />

        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {whyChooseFeatures.map((feature, index) => (
            <motion.div
              key={feature.title}
              initial={{ opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.6, delay: index * 0.08 }}
              className="text-center text-white p-8 lg:p-9 rounded-[20px] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 hover:-translate-y-2 hover:border-green-500/40 hover:shadow-xl transition-all"
            >
              <div className="w-16 h-16 lg:w-[72px] lg:h-[72px] rounded-full border-2 border-white/25 flex items-center justify-center mx-auto mb-5 text-green-500 group-hover:border-green-500 group-hover:bg-green-500/10 transition-colors">
                <Icon name={feature.icon} className="w-7 h-7 lg:w-8 lg:h-8" />
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
