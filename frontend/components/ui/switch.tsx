"use client";

import * as React from "react";
import { cn } from "@/lib/utils";

export interface SwitchProps
  extends Omit<React.InputHTMLAttributes<HTMLInputElement>, "type"> {
  label?: React.ReactNode;
}

const Switch = React.forwardRef<HTMLInputElement, SwitchProps>(
  ({ className, label, ...props }, ref) => {
    return (
      <label className={cn("inline-flex items-center gap-3 cursor-pointer", className)}>
        <div className="relative inline-flex h-6 w-11 items-center">
          <input
            type="checkbox"
            className="peer sr-only"
            ref={ref}
            {...props}
          />
          <span className="absolute inset-0 rounded-full bg-neutral-300 transition-colors-base peer-checked:bg-secondary-500 peer-focus-visible:ring-2 peer-focus-visible:ring-secondary-500 peer-focus-visible:ring-offset-2" />
          <span className="absolute left-1 h-4 w-4 rounded-full bg-white transition-transform-base peer-checked:translate-x-5" />
        </div>
        {label && (
          <span className="text-sm font-medium text-text-heading">{label}</span>
        )}
      </label>
    );
  }
);
Switch.displayName = "Switch";

export { Switch };
