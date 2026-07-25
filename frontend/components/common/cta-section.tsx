"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface CTASectionProps {
  title: string;
  description?: string;
  buttonText: string;
  buttonHref: string;
  secondaryButton?: { text: string; href: string };
  light?: boolean;
  className?: string;
}

export function CTASection({
  title,
  description,
  buttonText,
  buttonHref,
  secondaryButton,
  light = false,
  className,
}: CTASectionProps) {
  return (
    <section
      className={cn("py-20 lg:py-28", className)}
      style={{
        background: light
          ? "linear-gradient(135deg, var(--neutral-50) 0%, var(--surface-card) 100%)"
          : "linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 100%)",
      }}
    >
      <Container>
        <motion.div
          initial={{ opacity: 0, y: 40 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, margin: "-100px" }}
          transition={{ duration: 0.7 }}
          className="max-w-3xl mx-auto text-center px-6 py-12 lg:px-12 lg:py-16 rounded-[28px] border shadow-lg"
          style={{
            background: light ? "var(--surface-card)" : "rgba(255,255,255,0.03)",
            borderColor: light ? "var(--border-default)" : "rgba(255,255,255,0.1)",
          }}
        >
          <h2
            className={cn(
              "text-2xl sm:text-3xl lg:text-4xl font-extrabold mb-4 tracking-tight",
              light ? "text-text-heading" : "text-white"
            )}
          >
            {title}
          </h2>
          {description && (
            <p
              className={cn(
                "text-base lg:text-lg mb-8 leading-relaxed",
                light ? "text-text-muted" : "text-white/75"
              )}
            >
              {description}
            </p>
          )}
          <div className="flex flex-wrap justify-center gap-4">
            <Link
              href={buttonHref}
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-1 transition-transform-base group"
            >
              {buttonText}
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />
            </Link>
            {secondaryButton && (
              <Link
                href={secondaryButton.href}
                className={cn(
                  "inline-flex items-center px-7 py-3.5 rounded-full font-semibold border hover:-translate-y-1 transition-all-base",
                  light
                    ? "border-border-default text-text-heading hover:bg-neutral-50"
                    : "border-white/40 text-white hover:bg-white/10"
                )}
              >
                {secondaryButton.text}
              </Link>
            )}
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
