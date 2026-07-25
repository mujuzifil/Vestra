import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";

const badgeVariants = cva(
  "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors-base",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-secondary-100 text-secondary-600 hover:bg-secondary-200",
        secondary:
          "border-transparent bg-primary-100 text-primary-600 hover:bg-primary-200",
        accent:
          "border-transparent bg-accent-100 text-accent-600 hover:bg-accent-200",
        outline:
          "border-border-default text-text-muted hover:bg-neutral-50",
        success:
          "border-transparent bg-success-100 text-success-600 hover:bg-success-200",
        warning:
          "border-transparent bg-warning-100 text-warning-600 hover:bg-warning-200",
        danger:
          "border-transparent bg-danger-100 text-danger-600 hover:bg-danger-200",
        info:
          "border-transparent bg-info-100 text-info-600 hover:bg-info-200",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof badgeVariants> {}

function Badge({ className, variant, ...props }: BadgeProps) {
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props} />
  );
}

export { Badge, badgeVariants };
