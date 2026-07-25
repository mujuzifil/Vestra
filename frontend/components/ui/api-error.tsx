"use client";

import { AlertTriangle, RefreshCw } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "./button";

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
      <AlertTriangle className="w-12 h-12 text-warning-500 mb-4" aria-hidden="true" />
      <h3 className="text-lg font-bold text-text-heading mb-2">Something went wrong</h3>
      <p className="text-text-muted max-w-md mb-6">
        {message || "We couldn't load the data. Please check your connection and try again."}
      </p>
      {onRetry && (
        <Button onClick={onRetry} variant="outline" leftIcon={<RefreshCw className="w-4 h-4" />}>
          Try Again
        </Button>
      )}
    </div>
  );
}
