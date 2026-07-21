"use client";

import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Breadcrumb } from "@/components/common/breadcrumb";
import { cn } from "@/lib/utils";

interface PageHeroProps {
  title: string;
  subtitle?: string;
  breadcrumb?: { label: string; href?: string }[];
  className?: string;
}

export function PageHero({ title, subtitle, breadcrumb, className }: PageHeroProps) {
  return (
    <section
      className={cn(
        "relative pt-32 pb-20 lg:pt-44 lg:pb-28 overflow-hidden",
        className
      )}
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

      {breadcrumb && <Breadcrumb items={breadcrumb} className="relative z-10" />}

      <Container className="relative z-10">
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7 }}
          className={cn("text-center", !breadcrumb && "pt-8")}
        >
          <h1 className="text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold text-white mb-4 tracking-tight">
            {title}
          </h1>
          {subtitle && (
            <p className="text-base lg:text-xl text-white/75 max-w-2xl mx-auto leading-relaxed">
              {subtitle}
            </p>
          )}
          <div className="w-20 h-1 bg-gradient-to-r from-green-500 to-[#7db8ec] rounded-full mx-auto mt-6" />
        </motion.div>
      </Container>
    </section>
  );
}
