import { cn } from "@/lib/utils";
import { Loader2 } from "lucide-react";

interface SpinnerProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: "xs" | "sm" | "md" | "lg" | "xl";
  color?: "primary" | "secondary" | "white" | "muted";
}

const sizeClasses = {
  xs: "h-3 w-3",
  sm: "h-4 w-4",
  md: "h-6 w-6",
  lg: "h-8 w-8",
  xl: "h-10 w-10",
};

const colorClasses = {
  primary: "text-secondary-500",
  secondary: "text-primary-500",
  white: "text-white",
  muted: "text-text-muted",
};

function Spinner({
  className,
  size = "md",
  color = "primary",
  ...props
}: SpinnerProps) {
  return (
    <div
      role="status"
      className={cn("inline-flex items-center justify-center", className)}
      {...props}
    >
      <Loader2
        className={cn("animate-spinner", sizeClasses[size], colorClasses[color])}
        aria-hidden="true"
      />
      <span className="sr-only">Loading...</span>
    </div>
  );
}

export { Spinner };
