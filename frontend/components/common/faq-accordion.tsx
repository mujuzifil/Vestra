"use client";

import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Plus, Minus } from "lucide-react";
import { cn } from "@/lib/utils";
import { FaqItem } from "@/types";

interface FAQAccordionProps {
  items: FaqItem[];
  className?: string;
}

export function FAQAccordion({ items, className }: FAQAccordionProps) {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  return (
    <div className={cn("space-y-4", className)}>
      {items.map((item, index) => {
        const isOpen = openIndex === index;
        return (
          <div
            key={index}
            className={cn(
              "rounded-[16px] border bg-surface-card overflow-hidden transition-all-base",
              isOpen ? "border-primary-300 shadow-md" : "border-border shadow-sm"
            )}
          >
            <button
              onClick={() => setOpenIndex(isOpen ? null : index)}
              className="w-full flex items-center justify-between gap-4 p-5 lg:p-6 text-left"
              aria-expanded={isOpen}
            >
              <span className="font-semibold text-primary-900 text-base lg:text-lg">{item.question}</span>
              <span
                className={cn(
                  "w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 transition-colors-base",
                  isOpen ? "bg-secondary-500 text-white" : "bg-neutral-100 text-muted"
                )}
              >
                {isOpen ? <Minus className="w-4 h-4" /> : <Plus className="w-4 h-4" />}
              </span>
            </button>
            <AnimatePresence initial={false}>
              {isOpen && (
                <motion.div
                  initial={{ height: 0, opacity: 0 }}
                  animate={{ height: "auto", opacity: 1 }}
                  exit={{ height: 0, opacity: 0 }}
                  transition={{ duration: 0.3 }}
                >
                  <p className="px-5 lg:px-6 pb-5 lg:pb-6 text-neutral-600 leading-relaxed">{item.answer}</p>
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        );
      })}
    </div>
  );
}
