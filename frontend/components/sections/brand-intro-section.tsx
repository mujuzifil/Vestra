"use client";

import Image from "next/image";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Shield } from "lucide-react";

export function BrandIntroSection() {
  return (
    <section id="about" className="py-24 lg:py-36 bg-white">
      <Container>
        <div className="grid md:grid-cols-2 lg:grid-cols-[0.9fr_1.3fr_0.5fr] gap-12 lg:gap-16 items-center">
          <motion.div
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7 }}
            className="flex justify-center"
          >
            <Image
              src="/assets/images/branding/vestra-logo.png"
              alt="VESTRA"
              width={220}
              height={90}
              sizes="(max-width: 768px) 180px, 220px"
              className="max-h-[180px] lg:max-h-[220px] w-auto object-contain drop-shadow-xl"
            />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.1 }}
            className="text-center md:text-left"
          >
            <p className="text-lg lg:text-xl font-medium text-[#0a1628] leading-relaxed mb-5">
              VESTRA exists to redefine fabric care through innovation, quality, and performance. We
              develop professional-grade detergents and garment care solutions for laundries,
              businesses, and consumers who expect more than ordinary cleaning.
            </p>
            <p className="text-[#475569] text-base lg:text-lg leading-relaxed">
              Rather than focusing only on removing dirt, VESTRA aims to preserve fabrics, extend
              garment life, and deliver a premium cleaning experience.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 40 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, margin: "-100px" }}
            transition={{ duration: 0.7, delay: 0.2 }}
            className="flex justify-center md:justify-start lg:justify-center"
          >
            <div className="relative w-[170px] h-[210px] rounded-[20px] bg-gradient-to-br from-[#0d1f51]/85 to-[#0d3b66]/75 backdrop-blur-sm text-white flex flex-col items-center justify-center text-center p-6 shadow-lg border border-[#d4af37]/40 overflow-hidden">
              <div className="absolute inset-2 border border-[#d4af37]/50 rounded-[12px]" />
              <Shield className="w-12 h-12 text-[#d4af37] mb-4 relative z-10" />
              <span className="text-base font-extrabold leading-tight text-[#f0d878] mb-2 relative z-10">
                PROFESSIONAL
                <br />
                QUALITY
              </span>
              <span className="text-sm text-white/85 font-medium leading-tight relative z-10">
                PREMIUM
                <br />
                RESULTS
              </span>
            </div>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
