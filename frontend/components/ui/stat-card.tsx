import * as React from "react";
import { cn } from "@/lib/utils";
import { LucideIcon } from "lucide-react";

interface StatCardProps extends React.HTMLAttributes<HTMLDivElement> {
  title: string;
  value: React.ReactNode;
  icon?: LucideIcon;
  trend?: {
    value: string;
    positive?: boolean;
  };
  variant?: "default" | "primary" | "secondary" | "accent" | "danger" | "success";
}

const variantClasses = {
  default: "bg-surface-card border-border-default",
  primary: "bg-primary-500 text-white border-primary-500",
  secondary: "bg-secondary-500 text-white border-secondary-500",
  accent: "bg-accent-500 text-white border-accent-500",
  danger: "bg-danger-500 text-white border-danger-500",
  success: "bg-success-500 text-white border-success-500",
};

const iconWrapperClasses = {
  default: "bg-neutral-100 text-primary-500",
  primary: "bg-white/20 text-white",
  secondary: "bg-white/20 text-white",
  accent: "bg-white/20 text-white",
  danger: "bg-white/20 text-white",
  success: "bg-white/20 text-white",
};

function StatCard({
  className,
  title,
  value,
  icon: Icon,
  trend,
  variant = "default",
  ...props
}: StatCardProps) {
  const isInverse = variant !== "default";
  return (
    <div
      className={cn(
        "rounded-xl border p-5 shadow-sm transition-shadow-base hover:shadow-md",
        variantClasses[variant],
        className
      )}
      {...props}
    >
      <div className="flex items-start justify-between">
        <div>
          <p
            className={cn(
              "text-sm font-medium",
              isInverse ? "text-white/80" : "text-text-muted"
            )}
          >
            {title}
          </p>
          <p className="mt-1 text-2xl font-bold tracking-tight">{value}</p>
          {trend && (
            <p className="mt-1 text-xs">
              <span
                className={cn(
                  trend.positive ? "text-success-500" : "text-danger-500",
                  isInverse && "text-white/90"
                )}
              >
                {trend.positive ? "+" : ""}
                {trend.value}
              </span>
            </p>
          )}
        </div>
        {Icon && (
          <div
            className={cn(
              "rounded-lg p-2.5",
              iconWrapperClasses[variant]
            )}
          >
            <Icon className="h-5 w-5" aria-hidden="true" />
          </div>
        )}
      </div>
    </div>
  );
}

export { StatCard };
