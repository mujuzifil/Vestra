"use client";

import { Award, Medal, Shield } from "lucide-react";
import { cn } from "@/lib/utils";

export type DistributorTierValue = "silver" | "gold" | "master" | null | undefined;

const tierConfig = {
  silver: {
    label: "Silver Distributor",
    className: "bg-slate-100 text-slate-800 border-slate-300",
    Icon: Shield,
  },
  gold: {
    label: "Gold Distributor",
    className: "bg-amber-50 text-amber-900 border-amber-300",
    Icon: Medal,
  },
  master: {
    label: "Master Distributor",
    className: "bg-emerald-50 text-emerald-900 border-emerald-300",
    Icon: Award,
  },
} as const;

interface DistributorTierBadgeProps {
  tier: DistributorTierValue;
  label?: string | null;
  size?: "sm" | "md";
  className?: string;
}

export function DistributorTierBadge({
  tier,
  label,
  size = "sm",
  className,
}: DistributorTierBadgeProps) {
  const config = tier && tier in tierConfig ? tierConfig[tier] : null;
  const Icon = config?.Icon ?? Shield;
  const text = label || config?.label || "Authorized Distributor";

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 rounded-full border font-semibold",
        size === "md" ? "px-3 py-1.5 text-sm" : "px-2.5 py-1 text-xs",
        config?.className ?? "bg-primary-50 text-primary-800 border-primary-200",
        className
      )}
    >
      <Icon className={size === "md" ? "h-4 w-4" : "h-3.5 w-3.5"} aria-hidden="true" />
      {text}
    </span>
  );
}
