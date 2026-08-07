"use client";

import Link from "next/link";
import { Home, ChevronRight } from "lucide-react";
import { Container } from "@/components/common/container";
import { cn } from "@/lib/utils";

interface BreadcrumbProps {
  items: { label: string; href?: string }[];
  className?: string;
}

export function Breadcrumb({ items, className }: BreadcrumbProps) {
  return (
    <nav aria-label="Breadcrumb" className={cn("py-3 sm:py-4", className)}>
      <Container className="min-w-0">
        <ol className="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-white/70 min-w-0 max-w-full">
          <li className="flex-shrink-0">
            <Link href="/" className="flex items-center gap-1 hover:text-secondary-400 transition-colors-base">
              <Home className="w-4 h-4" />
              <span className="hidden sm:inline">Home</span>
            </Link>
          </li>
          {items.map((item, index) => (
            <li key={`${item.label}-${index}`} className="flex items-center gap-2 min-w-0 max-w-full">
              <ChevronRight className="w-4 h-4 text-white/50 flex-shrink-0" />
              {item.href && index < items.length - 1 ? (
                <Link href={item.href} className="hover:text-secondary-400 transition-colors-base truncate max-w-[40vw] sm:max-w-none">
                  {item.label}
                </Link>
              ) : (
                <span className="text-white font-medium truncate max-w-[55vw] sm:max-w-md">{item.label}</span>
              )}
            </li>
          ))}
        </ol>
      </Container>
    </nav>
  );
}
