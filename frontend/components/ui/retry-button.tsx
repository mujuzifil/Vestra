"use client";

import { RefreshCw } from "lucide-react";
import { cn } from "@/lib/utils";

interface RetryButtonProps {
  onRetry: () => void;
  className?: string;
}

export function RetryButton({ onRetry, className }: RetryButtonProps) {
  return (
    <button
      onClick={onRetry}
      className={cn(
        "inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-semibold text-sm bg-white border border-[#e2e8f0] text-[#0a1628] hover:bg-[#f8fafc] hover:border-[#4a90d9] transition-colors",
        className
      )}
    >
      <RefreshCw className="w-4 h-4" />
      Try Again
    </button>
  );
}
