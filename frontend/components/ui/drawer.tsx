"use client";

import * as React from "react";
import { cn } from "@/lib/utils";
import { X } from "lucide-react";
import { useEffect } from "react";

interface DrawerProps {
  open: boolean;
  onClose: () => void;
  title?: React.ReactNode;
  description?: React.ReactNode;
  children: React.ReactNode;
  footer?: React.ReactNode;
  position?: "left" | "right";
  className?: string;
}

function Drawer({
  open,
  onClose,
  title,
  description,
  children,
  footer,
  position = "right",
  className,
}: DrawerProps) {
  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    };
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50" role="dialog" aria-modal="true">
      <div
        className="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity-base"
        onClick={onClose}
        aria-hidden="true"
      />
      <div
        className={cn(
          "absolute top-0 h-full w-full max-w-md bg-surface-card shadow-xl flex flex-col",
          position === "left" ? "left-0 animate-slide-right" : "right-0 animate-slide-left",
          className
        )}
      >
        <div className="flex items-start justify-between border-b border-border-default px-6 py-4">
          <div>
            {title && (
              <h2 className="text-lg font-semibold text-text-heading">{title}</h2>
            )}
            {description && (
              <p className="mt-1 text-sm text-text-muted">{description}</p>
            )}
          </div>
          <button
            onClick={onClose}
            className="rounded-md p-1 text-text-muted hover:bg-neutral-100 hover:text-text-heading transition-colors-base"
            aria-label="Close drawer"
          >
            <X className="h-5 w-5" />
          </button>
        </div>
        <div className="flex-1 overflow-y-auto px-6 py-4">{children}</div>
        {footer && (
          <div className="border-t border-border-default px-6 py-4">{footer}</div>
        )}
      </div>
    </div>
  );
}

export { Drawer };
