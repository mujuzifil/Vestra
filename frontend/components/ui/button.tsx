"use client";

import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { cn } from "@/lib/utils";
import { Loader2 } from "lucide-react";

const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-semibold transition-all-base focus-visible:ring-2 focus-visible:ring-secondary-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default:
          "bg-secondary-500 text-white hover:bg-secondary-600 shadow-sm",
        secondary:
          "bg-primary-500 text-white hover:bg-primary-600 shadow-sm",
        outline:
          "border border-border-default bg-surface-card text-text-heading hover:bg-surface-page hover:border-primary-400",
        ghost:
          "text-text-heading hover:bg-neutral-100 hover:text-primary-600",
        link: "text-secondary-500 underline-offset-4 hover:underline",
        danger: "bg-danger-500 text-white hover:bg-danger-600 shadow-sm",
        accent: "bg-accent-500 text-white hover:bg-accent-600 shadow-sm",
        gradient:
          "bg-gradient-to-br from-secondary-500 to-secondary-600 text-white shadow-lg shadow-secondary-500/30 hover:shadow-secondary-500/40 hover:-translate-y-0.5",
      },
      size: {
        default: "h-10 px-4 py-2",
        sm: "h-8 px-3 text-xs",
        lg: "h-12 px-6 text-base",
        icon: "h-10 w-10",
        "icon-sm": "h-8 w-8",
        "icon-lg": "h-12 w-12",
      },
      fullWidth: {
        true: "w-full",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  isLoading?: boolean;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      fullWidth,
      isLoading,
      leftIcon,
      rightIcon,
      children,
      disabled,
      asChild,
      ...props
    },
    ref
  ) => {
    const classes = cn(buttonVariants({ variant, size, fullWidth, className }));

    if (asChild && React.isValidElement(children)) {
      const child = children as React.ReactElement<{ className?: string; ref?: React.Ref<unknown>; children?: React.ReactNode }>;
      const childProps = child.props as { className?: string; children?: React.ReactNode };
      return React.cloneElement(child, {
        className: cn(classes, childProps.className),
        ref,
        "aria-disabled": disabled || isLoading ? true : undefined,
        ...props,
        children: (
          <>
            {!isLoading && leftIcon}
            {childProps.children}
            {!isLoading && rightIcon}
          </>
        ),
      });
    }

    return (
      <button
        className={classes}
        ref={ref}
        disabled={disabled || isLoading}
        {...props}
      >
        {isLoading && <Loader2 className="w-4 h-4 animate-spin" />}
        {!isLoading && leftIcon}
        {children}
        {!isLoading && rightIcon}
      </button>
    );
  }
);
Button.displayName = "Button";

export { Button, buttonVariants };
