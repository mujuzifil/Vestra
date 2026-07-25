import * as React from "react";
import { cn } from "@/lib/utils";
import { PackageOpen, LucideIcon } from "lucide-react";
import { Button } from "./button";

interface EmptyStateProps extends React.HTMLAttributes<HTMLDivElement> {
  icon?: LucideIcon;
  title?: string;
  description?: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}

function EmptyState({
  className,
  icon: Icon = PackageOpen,
  title = "Nothing here yet",
  description = "Items will appear here once they are available.",
  action,
  ...props
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        "flex flex-col items-center justify-center py-12 px-4 text-center",
        className
      )}
      {...props}
    >
      <div className="mb-4 rounded-full bg-neutral-100 p-4">
        <Icon className="h-8 w-8 text-text-muted" aria-hidden="true" />
      </div>
      <h3 className="text-base font-semibold text-text-heading mb-1">{title}</h3>
      <p className="text-sm text-text-muted max-w-md mb-6">{description}</p>
      {action && (
        <Button onClick={action.onClick} variant="outline">
          {action.label}
        </Button>
      )}
    </div>
  );
}

export { EmptyState };
