"use client";

import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

interface SectionHeaderProps {
  title: string;
  subtitle?: string;
  centered?: boolean;
  light?: boolean;
  className?: string;
  id?: string;
}

export function SectionHeader({
  title,
  subtitle,
  centered = true,
  light = false,
  className,
  id,
}: SectionHeaderProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 40 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-100px" }}
      transition={{ duration: 0.7 }}
      className={cn("mb-12 lg:mb-16", centered && "text-center", className)}
    >
      <h2
        id={id}
        className={cn(
          "text-3xl sm:text-4xl lg:text-[clamp(2.5rem,5vw,3.75rem)] font-extrabold tracking-tight mb-4",
          light ? "text-white" : "text-[#0a1628]"
        )}
      >
        {title}
      </h2>
      {centered && (
        <div
          className={cn(
            "w-20 h-1 rounded-full mx-auto",
            light
              ? "bg-gradient-to-r from-green-500 to-[#7db8ec]"
              : "bg-gradient-to-r from-[#0d3b66] to-green-500"
          )}
        />
      )}
      {subtitle && (
        <p
          className={cn(
            "text-base lg:text-lg max-w-2xl mt-5 leading-relaxed",
            centered && "mx-auto",
            light ? "text-white/75" : "text-[#64748b]"
          )}
        >
          {subtitle}
        </p>
      )}
    </motion.div>
  );
}
