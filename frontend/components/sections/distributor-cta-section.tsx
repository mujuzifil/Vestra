"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { ArrowRight } from "lucide-react";

export function DistributorCtaSection() {
  return (
    <section
      id="distributor"
      className="py-24 lg:py-32"
      style={{
        background: "linear-gradient(135deg, #f8fafc 0%, #ffffff 100%)",
      }}
    >
      <Container>
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto text-center px-6 py-14 lg:px-12 lg:py-16 bg-white rounded-[28px] border border-[#e2e8f0] shadow-lg"
        >
          <h2 className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-[#0a1628] mb-4 tracking-tight">
            Become a VESTRA Distributor
          </h2>
          <p className="text-base lg:text-xl text-[#64748b] mb-8 leading-relaxed">
            Join our growing network of professional fabric care partners across Uganda and beyond.
          </p>
          <Link
            href="/distributor"
            className="inline-flex items-center gap-2 px-8 py-4 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-transform group"
          >
            Apply Now
            <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </Link>
        </motion.div>
      </Container>
    </section>
  );
}
