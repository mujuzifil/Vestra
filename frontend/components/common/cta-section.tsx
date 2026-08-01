"use client";

import Link from "next/link";
import { motion } from "framer-motion";
import { Container } from "@/components/common/container";
import { Button } from "@/components/ui/button";
import { ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";
import { useReducedMotion } from "@/hooks/use-reduced-motion";

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
  const prefersReducedMotion = useReducedMotion();

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
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
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
            <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto group" rightIcon={<ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" />}>
              <Link href={buttonHref}>
                {buttonText}
              </Link>
            </Button>
            {secondaryButton && (
              <Button
                asChild
                variant="outline"
                className={cn(
                  "rounded-full px-7 py-3.5 h-auto border hover:-translate-y-0.5",
                  light
                    ? "border-border-default text-text-heading hover:bg-surface-page"
                    : "border-white/40 text-white bg-transparent hover:bg-white/10 hover:text-white hover:border-white/50"
                )}
              >
                <Link href={secondaryButton.href}>
                  {secondaryButton.text}
                </Link>
              </Button>
            )}
          </div>
        </motion.div>
      </Container>
    </section>
  );
}
