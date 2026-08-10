"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ChevronRight, Star } from "lucide-react";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { Button } from "@/components/ui/button";

const heroFeatures = [
  { icon: "Factory", title: "Manufactured", description: "in Uganda" },
  { icon: "Shield", title: "Professional", description: "Quality" },
  { icon: "FlaskConical", title: "Advanced", description: "Formulations" },
];

export function HeroSection() {
  const prefersReducedMotion = useReducedMotion();
  return (
    <section
      id="home"
      className="relative min-h-[640px] lg:min-h-[calc(100vh-72px)] flex items-center overflow-hidden pt-28 lg:pt-0"
      aria-labelledby="hero-heading"
    >
      {/* Full-bleed product atmosphere */}
      <div className="absolute inset-0 z-0">
        <Image
          src="/assets/images/hero/whitemax-hero.webp"
          alt=""
          fill
          priority
          sizes="100vw"
          className="object-cover object-[62%_center] sm:object-[70%_center] lg:object-[78%_center]"
          aria-hidden="true"
        />
        <div
          className="absolute inset-0"
          style={{
            background:
              "linear-gradient(105deg, rgba(3,17,40,0.94) 0%, rgba(3,17,40,0.88) 34%, rgba(3,17,40,0.55) 58%, rgba(3,17,40,0.28) 78%, rgba(3,17,40,0.18) 100%)",
          }}
        />
        <div
          className="absolute inset-0"
          style={{
            background:
              "radial-gradient(ellipse 70% 80% at 18% 45%, rgba(3,17,40,0.55) 0%, transparent 60%), radial-gradient(ellipse at center, transparent 50%, rgba(2,8,18,0.55) 100%)",
          }}
        />
      </div>

      <Container className="relative z-10 w-full py-16 lg:py-24">
        <div className="grid lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] gap-10 lg:gap-8 items-center">
          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="text-white max-w-full sm:max-w-xl min-w-0"
          >
            <div className="inline-flex max-w-full items-center gap-2 px-3 sm:px-4 py-2 rounded-full border border-white/20 bg-white/5 backdrop-blur-sm text-[11px] sm:text-sm font-bold tracking-wider mb-6">
              <Star className="w-4 h-4 text-secondary-500 fill-secondary-500 flex-shrink-0" aria-hidden="true" />
              <span className="truncate sm:whitespace-normal">WORLD-CLASS CLEANING SOLUTIONS</span>
            </div>

            <h1
              id="hero-heading"
              className="text-4xl sm:text-5xl lg:text-[clamp(2.75rem,5.4vw,4.75rem)] font-black leading-[1.05] tracking-tight mb-6"
            >
              Professional Cleaning Solutions.
              <br />
              <span className="text-secondary-500">Engineered for Every Standard.</span>
            </h1>

            <p className="text-white/80 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
              VESTRA® develops high-performance detergents and fabric-care solutions for businesses,
              institutions, and distribution partners demanding consistent quality, reliable supply,
              and professional results.
            </p>

            <div className="flex flex-wrap gap-3 sm:gap-4 mb-10">
              <Button asChild variant="gradient" className="rounded-full px-6 sm:px-7 py-3.5 h-auto group w-full sm:w-auto justify-center" rightIcon={<ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
                <Link href="/request-quote" data-track="hero-primary-cta">Request a Quote</Link>
              </Button>
              <Button asChild variant="outline" className="rounded-full px-6 py-3.5 h-auto border-white/40 text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 hover:text-white hover:border-white/50 w-full sm:w-auto justify-center" rightIcon={<ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
                <Link href="/distributor" data-track="hero-secondary-cta">Become a Distributor</Link>
              </Button>
              <Button asChild variant="outline" className="rounded-full px-6 py-3.5 h-auto border-white/40 text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 hover:text-white hover:border-white/50 w-full sm:w-auto justify-center" rightIcon={<ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
                <Link href="/where-to-buy" data-track="hero-where-to-buy-cta">Where to Buy</Link>
              </Button>
            </div>

            <div className="flex flex-wrap items-center gap-4 sm:gap-6">
              {heroFeatures.map((feature, index) => (
                <div key={feature.title} className="flex items-center gap-3">
                  <div className="w-10 h-10 sm:w-12 sm:h-12 rounded-full border border-secondary-500/40 bg-secondary-500/10 flex items-center justify-center text-secondary-500">
                    <Icon name={feature.icon} className="w-5 h-5" aria-hidden="true" />
                  </div>
                  <div className="leading-tight">
                    <strong className="block text-white text-sm font-semibold">{feature.title}</strong>
                    <span className="text-white/70 text-xs sm:text-sm">{feature.description}</span>
                  </div>
                  {index < heroFeatures.length - 1 && (
                    <div className="hidden sm:block w-px h-8 bg-white/20 ml-2" aria-hidden="true" />
                  )}
                </div>
              ))}
            </div>
          </motion.div>

          {/* Spacer keeps product visible on the right through the gradient */}
          <div className="hidden lg:block min-h-[420px]" aria-hidden="true" />
        </div>
      </Container>
    </section>
  );
}
