"use client";

import { AlertTriangle, RefreshCw } from "lucide-react";
import { cn } from "@/lib/utils";

interface ApiErrorProps {
  message?: string;
  onRetry?: () => void;
  className?: string;
}

export function ApiError({ message, onRetry, className }: ApiErrorProps) {
  return (
    <div
      className={cn(
        "flex flex-col items-center justify-center py-16 px-4 text-center",
        className
      )}
    >
      <AlertTriangle className="w-12 h-12 text-amber-500 mb-4" aria-hidden="true" />
      <h3 className="text-lg font-bold text-[#0a1628] mb-2">Something went wrong</h3>
      <p className="text-[#64748b] max-w-md mb-6">
        {message || "We couldn't load the data. Please check your connection and try again."}
      </p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="inline-flex items-center gap-2 px-6 py-2.5 rounded-full font-semibold text-sm bg-white border border-[#e2e8f0] text-[#0a1628] hover:bg-[#f8fafc] hover:border-[#4a90d9] transition-colors"
        >
          <RefreshCw className="w-4 h-4" />
          Try Again
        </button>
      )}
    </div>
  );
}
