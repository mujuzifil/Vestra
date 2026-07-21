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
        "flex flex-col items-center justify-center text-center py-16 px-6 rounded-[20px] bg-[#f8fafc] border border-[#e2e8f0]",
        className
      )}
    >
      <div className="w-14 h-14 rounded-full bg-[#e2e8f0] flex items-center justify-center text-[#64748b] mb-4">
        <AlertCircle className="w-7 h-7" />
      </div>
      <h3 className="text-lg font-bold text-[#0a1628] mb-2">{title}</h3>
      {description && <p className="text-sm text-[#64748b] max-w-md">{description}</p>}
    </div>
  );
}
