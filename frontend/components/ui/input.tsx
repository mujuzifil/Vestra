import * as React from "react";
import { cn } from "@/lib/utils";

export type InputProps = React.InputHTMLAttributes<HTMLInputElement>;

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        className={cn(
          "flex h-10 w-full rounded-md border border-border-default bg-surface-card px-3 py-2 text-sm text-text-heading shadow-sm transition-colors-base",
          "placeholder:text-text-placeholder",
          "focus:border-border-focus focus:outline-none focus:ring-1 focus:ring-border-focus",
          "disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:opacity-50",
          "file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-text-heading",
          className
        )}
        ref={ref}
        {...props}
      />
    );
  }
);
Input.displayName = "Input";

export { Input };
