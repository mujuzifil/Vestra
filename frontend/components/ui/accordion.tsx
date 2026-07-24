"use client";

import * as React from "react";
import { cn } from "@/lib/utils";
import { ChevronDown } from "lucide-react";

interface AccordionItem {
  id: string;
  title: React.ReactNode;
  content: React.ReactNode;
}

interface AccordionProps extends React.HTMLAttributes<HTMLDivElement> {
  items: AccordionItem[];
  defaultOpen?: string[];
  allowMultiple?: boolean;
}

function Accordion({
  className,
  items,
  defaultOpen = [],
  allowMultiple = false,
  ...props
}: AccordionProps) {
  const [openItems, setOpenItems] = React.useState<Set<string>>(
    new Set(defaultOpen)
  );

  const toggle = (id: string) => {
    setOpenItems((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        if (!allowMultiple) next.clear();
        next.add(id);
      }
      return next;
    });
  };

  return (
    <div className={cn("divide-y divide-border-default rounded-lg border border-border-default", className)} {...props}>
      {items.map((item) => {
        const isOpen = openItems.has(item.id);
        return (
          <div key={item.id} className="bg-surface-card">
            <button
              type="button"
              onClick={() => toggle(item.id)}
              className="flex w-full items-center justify-between px-5 py-4 text-left"
              aria-expanded={isOpen}
            >
              <span className="text-sm font-semibold text-text-heading">
                {item.title}
              </span>
              <ChevronDown
                className={cn(
                  "h-4 w-4 text-text-muted transition-transform-base",
                  isOpen && "rotate-180"
                )}
              />
            </button>
            <div
              className={cn(
                "overflow-hidden transition-all-base",
                isOpen ? "max-h-screen opacity-100" : "max-h-0 opacity-0"
              )}
            >
              <div className="px-5 pb-4 text-sm text-text-body">
                {item.content}
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}

export { Accordion, type AccordionItem };
