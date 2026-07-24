"use client";

import { motion } from "framer-motion";
import { Icon } from "@/components/common/icon";
import { cn } from "@/lib/utils";

interface ValueCardProps {
  icon: string;
  title: string;
  description: string;
  index?: number;
  className?: string;
}

export function ValueCard({ icon, title, description, index = 0, className }: ValueCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 40 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-100px" }}
      transition={{ duration: 0.6, delay: index * 0.1 }}
      className={cn(
        "p-6 lg:p-8 rounded-[20px] bg-surface-card border border-border shadow-sm hover:-translate-y-1.5 hover:shadow-md hover:border-primary-300/50 transition-all-base text-center",
        className
      )}
    >
      <div className="w-16 h-16 lg:w-20 lg:h-20 rounded-full bg-gradient-to-br from-primary-500 to-primary-400 flex items-center justify-center text-white mx-auto mb-5 shadow-lg shadow-primary-400/25">
        <Icon name={icon} className="w-7 h-7 lg:w-8 lg:h-8" />
      </div>
      <h3 className="text-lg lg:text-xl font-bold text-primary-900 mb-2">{title}</h3>
      <p className="text-sm lg:text-base text-muted leading-relaxed">{description}</p>
    </motion.div>
  );
}
