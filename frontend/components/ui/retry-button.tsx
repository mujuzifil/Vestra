"use client";

import { RefreshCw } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "./button";

interface RetryButtonProps {
  onRetry: () => void;
  className?: string;
}

export function RetryButton({ onRetry, className }: RetryButtonProps) {
  return (
    <Button
      onClick={onRetry}
      variant="outline"
      className={cn("rounded-full", className)}
      leftIcon={<RefreshCw className="w-4 h-4" />}
    >
      Try Again
    </Button>
  );
}
