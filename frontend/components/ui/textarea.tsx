import * as React from "react";
import { cn } from "@/lib/utils";

export type TextareaProps = React.TextareaHTMLAttributes<HTMLTextAreaElement>;

const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>(
  ({ className, ...props }, ref) => {
    return (
      <textarea
        className={cn(
          "flex min-h-[80px] w-full rounded-md border border-border-default bg-surface-card px-3 py-2 text-sm text-text-heading shadow-sm transition-colors-base",
          "placeholder:text-text-placeholder",
          "focus:border-border-focus focus:outline-none focus:ring-1 focus:ring-border-focus",
          "disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:opacity-50",
          className
        )}
        ref={ref}
        {...props}
      />
    );
  }
);
Textarea.displayName = "Textarea";

export { Textarea };
