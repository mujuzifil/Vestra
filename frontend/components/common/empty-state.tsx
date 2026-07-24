"use client";

import { AlertCircle } from "lucide-react";
import { cn } from "@/lib/utils";

interface EmptyStateProps {
  title: string;
  description?: string;
  className?: string;
}

export function EmptyState({ title, description, className }: EmptyStateProps) {
  return (
    <div
      className={cn(
        "flex flex-col items-center justify-center text-center py-16 px-6 rounded-[20px] bg-neutral-50 border border-border-default",
        className
      )}
    >
      <div className="w-14 h-14 rounded-full bg-neutral-200 flex items-center justify-center text-text-muted mb-4">
        <AlertCircle className="w-7 h-7" />
      </div>
      <h3 className="text-lg font-bold text-text-heading mb-2">{title}</h3>
      {description && <p className="text-sm text-text-muted max-w-md">{description}</p>}
    </div>
  );
}
