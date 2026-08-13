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
  },
  {
    src: "/assets/images/hero/hero-slide-whitemax.webp",
    alt: "VESTRA WhiteMax professional laundry whitening solution",
  },
  {
    src: "/assets/images/hero/hero-slide-silkcare.webp",
    alt: "VESTRA Silk Care professional silk and luxury garment wash",
  },
] as const;

const SLIDE_INTERVAL_MS = 5000;
const SLIDE_FADE_MS = 0.9;

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
      className="relative overflow-hidden bg-primary-900 pt-[calc(72px+env(safe-area-inset-top))]"
      aria-labelledby="hero-heading"
    >
      <div className="pointer-events-none absolute inset-0 z-0" aria-hidden="true">
        <div
          className="absolute inset-0 opacity-50"
          style={{
            background:
              "radial-gradient(ellipse 70% 50% at 50% 0%, rgba(25,85,145,0.28) 0%, transparent 55%), radial-gradient(circle at 85% 70%, rgba(8,42,82,0.4) 0%, transparent 40%)",
          }}
        />
      </div>

      {/* Full-width slides — entire image visible, edge-to-edge */}
      <div className="relative z-10 w-full">
        <div className="relative w-full overflow-hidden bg-[#031128]">
          <div className="grid w-full">
            {heroSlides.map((slide, index) => (
              <motion.div
                key={slide.src}
                className="col-start-1 row-start-1 w-full"
                initial={false}
                animate={{ opacity: index === activeIndex ? 1 : 0 }}
                transition={{
                  duration: prefersReducedMotion ? 0 : SLIDE_FADE_MS,
                  ease: "easeInOut",
                }}
                aria-hidden={index !== activeIndex}
              >
                <Image
                  src={slide.src}
                  alt={slide.alt}
                  width={1600}
                  height={2000}
                  priority={index === 0}
                  sizes="100vw"
                  className="h-auto w-full"
                />
              </motion.div>
            ))}
          </div>

          {!prefersReducedMotion && (
            <div
              className="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 sm:bottom-5"
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
                      : "w-1.5 bg-white/45 hover:bg-white/75"
                  )}
                />
              ))}
            </div>
          )}
        </div>
      </div>

      <Container className="relative z-10 pb-16 pt-10 sm:pb-20 sm:pt-12 lg:pb-28 lg:pt-16">
        <motion.div
          initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, ease: "easeOut" }}
          className="mx-auto max-w-3xl text-center text-white"
        >
          <div className="mb-6 inline-flex max-w-full items-center gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-2 text-[11px] font-bold tracking-wider backdrop-blur-sm sm:px-4 sm:text-sm">
            <Star className="h-4 w-4 flex-shrink-0 fill-secondary-500 text-secondary-500" aria-hidden="true" />
            <span className="truncate sm:whitespace-normal">WORLD-CLASS CLEANING SOLUTIONS</span>
          </div>

          <h1
            id="hero-heading"
            className="mb-6 text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-[clamp(2.75rem,5vw,4.25rem)]"
          >
            Professional Cleaning Solutions.
            <br />
            <span className="text-secondary-500">Engineered for Every Standard.</span>
          </h1>

          <p className="mx-auto mb-10 max-w-2xl text-base leading-relaxed text-white/80 sm:text-lg">
            VESTRA® develops high-performance detergents and fabric-care solutions for businesses,
            institutions, and distribution partners demanding consistent quality, reliable supply,
            and professional results.
          </p>

          <div className="mb-12 flex flex-wrap justify-center gap-3 sm:gap-4">
            <Button
              asChild
              variant="gradient"
              className="h-auto w-full justify-center rounded-full px-6 py-3.5 group sm:w-auto sm:px-7"
              rightIcon={<ChevronRight className="h-4 w-4 transition-transform-base group-hover:translate-x-1" aria-hidden="true" />}
            >
              <Link href="/request-quote" data-track="hero-primary-cta">Request a Quote</Link>
            </Button>
            <Button
              asChild
              variant="outline"
              className="h-auto w-full justify-center rounded-full border-white/40 bg-white/10 px-6 py-3.5 text-white backdrop-blur-sm hover:border-white/50 hover:bg-white/20 hover:text-white sm:w-auto"
              rightIcon={<ChevronRight className="h-4 w-4 transition-transform-base group-hover:translate-x-1" aria-hidden="true" />}
            >
              <Link href="/distributor" data-track="hero-secondary-cta">Become a Distributor</Link>
            </Button>
            <Button
              asChild
              variant="outline"
              className="h-auto w-full justify-center rounded-full border-white/40 bg-white/10 px-6 py-3.5 text-white backdrop-blur-sm hover:border-white/50 hover:bg-white/20 hover:text-white sm:w-auto"
              rightIcon={<ChevronRight className="h-4 w-4 transition-transform-base group-hover:translate-x-1" aria-hidden="true" />}
            >
              <Link href="/where-to-buy" data-track="hero-where-to-buy-cta">Where to Buy</Link>
            </Button>
          </div>

          <div className="flex flex-wrap items-center justify-center gap-5 sm:gap-8">
            {heroFeatures.map((feature) => (
              <div key={feature.title} className="flex items-center gap-3 text-left">
                <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-secondary-500/40 bg-secondary-500/10 text-secondary-500 sm:h-12 sm:w-12">
                  <Icon name={feature.icon} className="h-5 w-5" aria-hidden="true" />
                </div>
                <div className="leading-tight">
                  <strong className="block text-sm font-semibold text-white">{feature.title}</strong>
                  <span className="text-xs text-white/70 sm:text-sm">{feature.description}</span>
                </div>
              </div>
            ))}
          </div>
        </motion.div>
      </Container>

      <span className="sr-only" aria-live="polite">
        {heroSlides[activeIndex]?.alt}
      </span>
    </section>
  );
}
