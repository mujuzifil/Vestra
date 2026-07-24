"use client";

import * as React from "react";
import { cn } from "@/lib/utils";
import { Check } from "lucide-react";

export interface CheckboxProps
  extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "type"> {
  label?: React.ReactNode;
  description?: React.ReactNode;
}

const Checkbox = React.forwardRef<HTMLInputElement, CheckboxProps>(
  ({ className, label, description, ...props }, ref) => {
    return (
      <label className={cn("flex items-start gap-3 cursor-pointer", className)}>
        <div className="relative flex items-center">
          <input
            type="checkbox"
            className="peer h-5 w-5 cursor-pointer appearance-none rounded border border-border-default bg-surface-card transition-colors-base checked:border-secondary-500 checked:bg-secondary-500 focus-visible:ring-2 focus-visible:ring-secondary-500 focus-visible:ring-offset-2"
            ref={ref}
            {...props}
          />
          <Check className="pointer-events-none absolute left-1/2 top-1/2 h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100" />
        </div>
        {(label || description) && (
          <div className="space-y-1 leading-none">
            {label && (
              <span className="text-sm font-medium text-text-heading">
                {label}
              </span>
            )}
            {description && (
              <p className="text-xs text-text-muted">{description}</p>
            )}
          </div>
        )}
      </label>
    );
  }
);
Checkbox.displayName = "Checkbox";

export { Checkbox };
