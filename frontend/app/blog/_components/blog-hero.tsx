"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, BookOpen } from "lucide-react";
import { Container } from "@/components/common/container";
import { Breadcrumb } from "@/components/common/breadcrumb";

export function BlogHero() {
  return (
    <section
      className="relative pt-28 pb-20 lg:pt-40 lg:pb-32 overflow-hidden"
      style={{
        background:
          "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 50%, var(--primary-500) 100%)",
      }}
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            "radial-gradient(circle at 20% 80%, rgba(112,192,80,0.12) 0%, transparent 45%), radial-gradient(circle at 80% 20%, rgba(13,59,102,0.6) 0%, transparent 40%)",
        }}
      />

      <Breadcrumb items={[{ label: "Blog" }]} className="relative z-10" />

      <Container className="relative z-10">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto text-center"
        >
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white/90 text-sm font-medium mb-6 backdrop-blur-sm border border-white/10">
            <BookOpen className="w-4 h-4" />
            Knowledge Centre
          </div>
          <h1 className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white mb-6 tracking-tight leading-tight">
            VESTRA® Knowledge Centre
          </h1>
          <p className="text-base lg:text-xl text-white/75 max-w-2xl mx-auto leading-relaxed mb-8">
            Expert advice on laundry, stain removal, detergent science, fabric care and commercial
            cleaning solutions for businesses and institutions.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              href="#articles"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white shadow-lg hover:-translate-y-1 transition-transform-base group"
            >
              Browse Articles
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />
            </Link>
            <Link
              href="/request-quote"
              className="inline-flex items-center px-7 py-3.5 rounded-full font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors-base"
            >
              Request a Quote
            </Link>
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
