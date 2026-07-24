import { cn } from "@/lib/utils";

interface SkeletonProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: "default" | "circle" | "text";
}

function Skeleton({ className, variant = "default", ...props }: SkeletonProps) {
  return (
    <div
      className={cn(
        "animate-skeleton rounded-md bg-neutral-200",
        variant === "circle" && "rounded-full",
        variant === "text" && "h-4 w-full",
        className
      )}
      {...props}
    />
  );
}

export { Skeleton };
