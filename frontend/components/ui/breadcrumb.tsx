import * as React from "react";
import { cn } from "@/lib/utils";
import { ChevronRight, Home } from "lucide-react";
import Link from "next/link";

interface BreadcrumbItem {
  label: string;
  href?: string;
}

interface BreadcrumbProps extends React.HTMLAttributes<HTMLElement> {
  items: BreadcrumbItem[];
  homeHref?: string;
}

function Breadcrumb({
  className,
  items,
  homeHref = "/",
  ...props
}: BreadcrumbProps) {
  return (
    <nav
      aria-label="Breadcrumb"
      className={cn("text-sm text-text-muted", className)}
      {...props}
    >
      <ol className="flex flex-wrap items-center gap-2">
        <li>
          <Link
            href={homeHref}
            className="flex items-center gap-1 hover:text-primary-500 transition-colors-base"
          >
            <Home className="h-4 w-4" />
            <span className="sr-only">Home</span>
          </Link>
        </li>
        {items.map((item, index) => {
          const isLast = index === items.length - 1;
          return (
            <li key={item.label + index} className="flex items-center gap-2">
              <ChevronRight className="h-4 w-4 text-neutral-300" />
              {isLast || !item.href ? (
                <span
                  className={cn(
                    "font-medium",
                    isLast ? "text-text-heading" : "text-text-muted"
                  )}
                  aria-current={isLast ? "page" : undefined}
                >
                  {item.label}
                </span>
              ) : (
                <Link
                  href={item.href}
                  className="hover:text-primary-500 transition-colors-base"
                >
                  {item.label}
                </Link>
              )}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}

export { Breadcrumb, type BreadcrumbItem };
