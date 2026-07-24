import { cn } from "@/lib/utils";
import { LucideIcon } from "lucide-react";

interface IconProps extends React.SVGAttributes<SVGSVGElement> {
  icon: LucideIcon;
  size?: "xs" | "sm" | "md" | "lg" | "xl";
  color?:
    | "default"
    | "muted"
    | "primary"
    | "secondary"
    | "accent"
    | "success"
    | "warning"
    | "danger"
    | "info"
    | "white";
}

const sizeClasses = {
  xs: "h-3 w-3",
  sm: "h-4 w-4",
  md: "h-5 w-5",
  lg: "h-6 w-6",
  xl: "h-8 w-8",
};

const colorClasses = {
  default: "text-text-heading",
  muted: "text-text-muted",
  primary: "text-primary-500",
  secondary: "text-secondary-500",
  accent: "text-accent-500",
  success: "text-success-500",
  warning: "text-warning-500",
  danger: "text-danger-500",
  info: "text-info-500",
  white: "text-white",
};

function Icon({
  icon: LucideIconComponent,
  size = "md",
  color = "default",
  className,
  ...props
}: IconProps) {
  return (
    <LucideIconComponent
      className={cn(sizeClasses[size], colorClasses[color], className)}
      aria-hidden="true"
      {...props}
    />
  );
}

export { Icon };
