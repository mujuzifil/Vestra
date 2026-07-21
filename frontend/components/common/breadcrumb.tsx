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
    <nav aria-label="Breadcrumb" className={cn("py-4", className)}>
      <Container>
        <ol className="flex flex-wrap items-center gap-2 text-sm text-white/70">
          <li>
            <Link href="/" className="flex items-center gap-1 hover:text-green-400 transition-colors">
              <Home className="w-4 h-4" />
              <span className="hidden sm:inline">Home</span>
            </Link>
          </li>
          {items.map((item, index) => (
            <li key={item.label} className="flex items-center gap-2">
              <ChevronRight className="w-4 h-4 text-white/50" />
              {item.href && index < items.length - 1 ? (
                <Link href={item.href} className="hover:text-green-400 transition-colors">
                  {item.label}
                </Link>
              ) : (
                <span className="text-white font-medium">{item.label}</span>
              )}
            </li>
          ))}
        </ol>
      </Container>
    </nav>
  );
}
