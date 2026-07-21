"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Quote } from "lucide-react";

export function VisionBannerSection() {
  return (
    <section
      className="relative py-24 lg:py-36 overflow-hidden"
      style={{
        background: "linear-gradient(135deg, #050d18 0%, #0d1f33 100%)",
      }}
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            "radial-gradient(circle at 30% 70%, rgba(112,192,80,0.1) 0%, transparent 50%), radial-gradient(circle at 70% 30%, rgba(255,255,255,0.05) 0%, transparent 40%)",
        }}
      />

      <Container className="relative z-10">
        <div className="grid lg:grid-cols-[1.3fr_0.7fr] gap-12 lg:gap-16 items-center">
          <motion.div
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7 }}
            className="relative text-white px-6 lg:px-8"
          >
            <Quote className="absolute -top-6 -left-2 lg:-top-8 lg:-left-4 w-10 h-10 lg:w-14 lg:h-14 text-green-500/50 fill-green-500/20" />
            <h2 className="text-xl sm:text-2xl lg:text-[clamp(1.75rem,3vw,2.5rem)] font-semibold leading-relaxed text-center lg:text-left">
              Building one of Africa&apos;s most respected fabric care brands through innovation,
              quality, and continuous improvement.
            </h2>
            <Quote className="absolute -bottom-6 right-0 lg:-bottom-8 lg:-right-2 w-10 h-10 lg:w-14 lg:h-14 text-green-500/50 fill-green-500/20 rotate-180" />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.2 }}
            className="relative flex justify-center items-end min-h-[180px] lg:min-h-[220px]"
          >
            <div className="w-44 h-14 rounded-xl bg-gradient-to-b from-white to-[#e2e8f0] shadow-2xl -translate-y-6 -rotate-6 z-10" />
            <div className="absolute w-44 h-14 rounded-xl bg-gradient-to-b from-green-500 to-green-600 shadow-2xl -rotate-2 z-0" />
            <span className="absolute top-0 right-[15%] text-5xl lg:text-6xl text-white/85 animate-bounce">
              &#10047;
            </span>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
