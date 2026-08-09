"use client";

import { useEffect } from "react";
import Link from "next/link";
import { CheckCircle, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export interface SubmissionSuccessAction {
  label: string;
  href?: string;
  onClick?: () => void;
  variant?: "gradient" | "outline";
}

interface SubmissionSuccessDialogProps {
  open: boolean;
  title: string;
  description: string;
  referenceLabel?: string;
  reference?: string | null;
  primaryAction?: SubmissionSuccessAction;
  secondaryAction?: SubmissionSuccessAction;
  onClose: () => void;
}

export function SubmissionSuccessDialog({
  open,
  title,
  description,
  referenceLabel = "Reference",
  reference,
  primaryAction,
  secondaryAction,
  onClose,
}: SubmissionSuccessDialogProps) {
  useEffect(() => {
    if (!open) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") onClose();
    };
    window.addEventListener("keydown", onKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", onKeyDown);
    };
  }, [open, onClose]);

  if (!open) return null;

  const renderAction = (action: SubmissionSuccessAction, className?: string) => {
    const variant = action.variant ?? "outline";
    if (action.href) {
      return (
        <Button
          asChild
          variant={variant}
          className={cn("rounded-full px-6 py-3 h-auto flex-1", className)}
        >
          <Link href={action.href} onClick={onClose}>
            {action.label}
          </Link>
        </Button>
      );
    }

    return (
      <Button
        type="button"
        variant={variant}
        className={cn("rounded-full px-6 py-3 h-auto flex-1", className)}
        onClick={() => {
          action.onClick?.();
          onClose();
        }}
      >
        {action.label}
      </Button>
    );
  };

  return (
    <div
      className="fixed inset-0 z-[80] flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="submission-success-title"
      aria-describedby="submission-success-description"
    >
      <button
        type="button"
        className="absolute inset-0 bg-primary-950/55 backdrop-blur-[2px]"
        aria-label="Close success dialog"
        onClick={onClose}
      />

      <div className="relative z-10 w-full max-w-md rounded-[24px] border border-border bg-white p-6 shadow-2xl sm:p-8">
        <button
          type="button"
          onClick={onClose}
          className="absolute right-3 top-3 rounded-full p-2 text-text-muted hover:bg-surface-page hover:text-text-heading"
          aria-label="Close"
        >
          <X className="h-5 w-5" />
        </button>

        <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secondary-500/10">
          <CheckCircle className="h-8 w-8 text-secondary-600" aria-hidden="true" />
        </div>

        <h2
          id="submission-success-title"
          className="text-center text-2xl font-extrabold text-text-heading"
        >
          {title}
        </h2>
        <p
          id="submission-success-description"
          className="mt-2 text-center text-sm leading-relaxed text-text-muted sm:text-base"
        >
          {description}
        </p>

        {reference ? (
          <div className="mt-5 rounded-2xl border border-border bg-surface-page px-4 py-3 text-center">
            <p className="text-xs font-semibold uppercase tracking-wider text-text-muted">
              {referenceLabel}
            </p>
            <p className="mt-1 font-mono text-base font-bold text-text-heading">{reference}</p>
          </div>
        ) : null}

        <div className="mt-6 flex flex-col gap-3 sm:flex-row">
          {primaryAction ? renderAction(primaryAction, undefined) : null}
          {secondaryAction ? renderAction(secondaryAction) : null}
          {!primaryAction && !secondaryAction ? (
            <Button
              type="button"
              variant="gradient"
              className="h-auto flex-1 rounded-full px-6 py-3"
              onClick={onClose}
            >
              Continue
            </Button>
          ) : null}
        </div>
      </div>
    </div>
  );
}
