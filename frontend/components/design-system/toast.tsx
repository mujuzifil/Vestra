"use client";

import { Toaster as SonnerToaster, toast as sonnerToast } from "sonner";

type ToastType = "success" | "error" | "warning" | "info";

interface ToastOptions {
  description?: string;
  duration?: number;
  action?: {
    label: string;
    onClick: () => void;
  };
}

function Toaster() {
  return (
    <SonnerToaster
      position="top-right"
      toastOptions={{
        classNames: {
          toast:
            "group toast bg-surface-card border border-border-default text-text-heading shadow-lg rounded-xl",
          description: "text-text-muted",
          actionButton:
            "bg-secondary-500 text-white hover:bg-secondary-600",
          cancelButton:
            "bg-neutral-100 text-text-heading hover:bg-neutral-200",
          success: "border-success-200 bg-success-50 text-success-700",
          error: "border-danger-200 bg-danger-50 text-danger-700",
          warning: "border-warning-200 bg-warning-50 text-warning-700",
          info: "border-info-200 bg-info-50 text-info-700",
        },
      }}
    />
  );
}

function toast(type: ToastType, title: string, options?: ToastOptions) {
  const common = {
    description: options?.description,
    duration: options?.duration,
    action: options?.action
      ? {
          label: options.action.label,
          onClick: options.action.onClick,
        }
      : undefined,
  };

  switch (type) {
    case "success":
      sonnerToast.success(title, common);
      break;
    case "error":
      sonnerToast.error(title, common);
      break;
    case "warning":
      sonnerToast.warning(title, common);
      break;
    case "info":
    default:
      sonnerToast.info(title, common);
      break;
  }
}

export { Toaster, toast };
