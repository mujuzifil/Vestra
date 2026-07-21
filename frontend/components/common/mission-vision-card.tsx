"use client";

import { motion } from "framer-motion";
import { Icon } from "@/components/common/icon";
import { cn } from "@/lib/utils";

interface MissionVisionCardProps {
  icon: string;
  label: string;
  title: string;
  description: string;
  className?: string;
}

export function MissionVisionCard({
  icon,
  label,
  title,
  description,
  className,
}: MissionVisionCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 40 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, margin: "-100px" }}
      transition={{ duration: 0.7 }}
      className={cn(
        "relative p-8 lg:p-10 rounded-[24px] overflow-hidden text-white",
        className
      )}
      style={{
        background: "linear-gradient(135deg, #0d3b66 0%, #0a1628 100%)",
      }}
    >
      <div
        className="absolute inset-0 pointer-events-none opacity-30"
        style={{
          background:
            "radial-gradient(circle at 80% 20%, rgba(112,192,80,0.2) 0%, transparent 40%)",
        }}
      />
      <div className="relative z-10">
        <span className="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-green-400 mb-4">
          {label}
        </span>
        <div className="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center text-white mb-6">
          <Icon name={icon} className="w-7 h-7" />
        </div>
        <h3 className="text-xl lg:text-2xl font-bold mb-3">{title}</h3>
        <p className="text-white/80 leading-relaxed text-base lg:text-lg">{description}</p>
      </div>
    </motion.div>
  );
}
