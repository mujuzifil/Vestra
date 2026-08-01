"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, MapPin } from "lucide-react";
import { Container } from "@/components/common/container";
import { Breadcrumb } from "@/components/common/breadcrumb";
import { Button } from "@/components/ui/button";

export function WhereToBuyHero() {
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

      <Breadcrumb items={[{ label: "Where to Buy" }]} className="relative z-10" />

      <Container className="relative z-10">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto text-center"
        >
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white/90 text-sm font-medium mb-6 backdrop-blur-sm border border-white/10">
            <MapPin className="w-4 h-4" />
            Distributor Locator
          </div>
          <h1 className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white mb-6 tracking-tight leading-tight">
            Find VESTRA® Products Near You
          </h1>
          <p className="text-base lg:text-xl text-white/75 max-w-2xl mx-auto leading-relaxed mb-8">
            VESTRA® products are supplied through an expanding network of authorised distributors and retail partners across Uganda.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto bg-white text-text-heading border-transparent hover:bg-white/90" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />}>
              <Link href="#directory">Find a Distributor</Link>
            </Button>
            <Button asChild variant="outline" className="rounded-full px-7 py-3.5 h-auto border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50">
              <Link href="/distributor">Become a Distributor</Link>
            </Button>
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
