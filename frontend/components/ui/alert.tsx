import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";
import { AlertCircle, CheckCircle2, Info, AlertTriangle, X } from "lucide-react";

const alertVariants = cva(
  "relative w-full rounded-lg border p-4 [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:h-5 [&>svg]:w-5 [&>svg+div]:translate-y-[-3px] [&:has(svg)]:pl-11",
  {
    variants: {
      variant: {
        default: "bg-neutral-50 border-border-default text-text-heading",
        success:
          "border-success-200 bg-success-50 text-success-700 [&>svg]:text-success-500",
        warning:
          "border-warning-200 bg-warning-50 text-warning-700 [&>svg]:text-warning-500",
        danger:
          "border-danger-200 bg-danger-50 text-danger-700 [&>svg]:text-danger-500",
        info: "border-info-200 bg-info-50 text-info-700 [&>svg]:text-info-500",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

const iconMap = {
  default: Info,
  success: CheckCircle2,
  warning: AlertTriangle,
  danger: AlertCircle,
  info: Info,
};

export interface AlertProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof alertVariants> {
  title?: string;
  onClose?: () => void;
}

function Alert({
  className,
  variant = "default",
  title,
  children,
  onClose,
  ...props
}: AlertProps) {
  const Icon = iconMap[variant || "default"];
  return (
    <div
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    >
      <Icon aria-hidden="true" />
      <div className="pr-6">
        {title && <h5 className="mb-1 font-semibold leading-none">{title}</h5>}
        <div className="text-sm">{children}</div>
      </div>
      {onClose && (
        <button
          onClick={onClose}
          className="absolute right-3 top-3 rounded-md p-1 text-text-muted hover:bg-black/5 hover:text-text-heading transition-colors-base"
          aria-label="Dismiss alert"
        >
          <X className="h-4 w-4" />
        </button>
      )}
    </div>
  );
}

export { Alert, alertVariants };
