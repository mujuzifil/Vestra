"use client";

import { useEffect, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ChevronRight, Star } from "lucide-react";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";
import { useReducedMotion } from "@/hooks/use-reduced-motion";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

const heroFeatures = [
  { icon: "Factory", title: "Manufactured", description: "in Uganda" },
  { icon: "Shield", title: "Professional", description: "Quality" },
  { icon: "FlaskConical", title: "Advanced", description: "Formulations" },
];

const heroSlides = [
  {
    src: "/assets/images/hero/home-page-image.webp",
    alt: "VESTRA professional detergent product range manufactured in Uganda",
    objectPosition: "object-[70%_center] sm:object-[75%_center] lg:object-[80%_center]",
  },
  {
    src: "/assets/images/hero/hero-slide-whitemax.webp",
    alt: "VESTRA WhiteMax professional laundry whitening solution",
    objectPosition: "object-[55%_center] sm:object-[60%_center] lg:object-[65%_center]",
  },
  {
    src: "/assets/images/hero/hero-slide-silkcare.webp",
    alt: "VESTRA Silk Care professional silk and luxury garment wash",
    objectPosition: "object-[50%_center] sm:object-[55%_center] lg:object-[60%_center]",
  },
] as const;

const SLIDE_INTERVAL_MS = 2000;

export function HeroSection() {
  const prefersReducedMotion = useReducedMotion();
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    if (prefersReducedMotion || heroSlides.length <= 1) return;

    const timer = window.setInterval(() => {
      setActiveIndex((current) => (current + 1) % heroSlides.length);
    }, SLIDE_INTERVAL_MS);

    return () => window.clearInterval(timer);
  }, [prefersReducedMotion]);

  return (
    <section
      id="home"
      className="relative min-h-[680px] sm:min-h-[720px] lg:min-h-[calc(100vh-72px)] flex items-center overflow-hidden pt-28 lg:pt-0"
      aria-labelledby="hero-heading"
    >
      {/* Full-bleed rotating product atmosphere */}
      <div className="absolute inset-0 z-0" aria-hidden="true">
        {heroSlides.map((slide, index) => (
          <motion.div
            key={slide.src}
            className="absolute inset-0"
            initial={false}
            animate={{ opacity: index === activeIndex ? 1 : 0 }}
            transition={{ duration: prefersReducedMotion ? 0 : 0.7, ease: "easeInOut" }}
          >
            <Image
              src={slide.src}
              alt=""
              fill
              priority={index === 0}
              sizes="100vw"
              className={cn("object-cover", slide.objectPosition)}
            />
          </motion.div>
        ))}

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
              "radial-gradient(ellipse 70% 80% at 18% 45%, rgba(3,17,40,0.55) 0%, transparent 60%), linear-gradient(to top, rgba(2,8,18,0.72) 0%, transparent 42%), radial-gradient(ellipse at center, transparent 50%, rgba(2,8,18,0.45) 100%)",
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

          {/* Keeps product visible through the gradient on large screens */}
          <div className="hidden lg:block min-h-[420px]" aria-hidden="true" />
        </div>
      </Container>

      {!prefersReducedMotion && (
        <div
          className="absolute bottom-6 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 lg:bottom-8 lg:left-auto lg:right-8 lg:translate-x-0"
          role="tablist"
          aria-label="Hero image slides"
        >
          {heroSlides.map((slide, index) => (
            <button
              key={slide.src}
              type="button"
              role="tab"
              aria-selected={index === activeIndex}
              aria-label={`Show slide ${index + 1}`}
              onClick={() => setActiveIndex(index)}
              className={cn(
                "h-1.5 rounded-full transition-all duration-300",
                index === activeIndex
                  ? "w-7 bg-secondary-500"
                  : "w-1.5 bg-white/40 hover:bg-white/70"
              )}
            />
          ))}
        </div>
      )}

      <span className="sr-only" aria-live="polite">
        {heroSlides[activeIndex]?.alt}
      </span>
    </section>
  );
}
