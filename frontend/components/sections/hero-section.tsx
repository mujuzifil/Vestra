"use client";

import { useEffect, useRef, useState, type KeyboardEvent, type ReactNode } from "react";
import Image from "next/image";
import Link from "next/link";
import { AnimatePresence, motion } from "framer-motion";
import { ChevronLeft, ChevronRight, Star } from "lucide-react";
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
    src: "/assets/images/hero/hero-slide-whitemax.webp",
    alt: "VESTRA WhiteMax — brighter whites professional laundry whitening",
    kicker: "WORLD-CLASS CLEANING SOLUTIONS",
    headline: "Professional Cleaning Solutions",
    highlight: "Engineered for Brighter Whites",
    description:
      "WhiteMax restores brilliant whites for commercial and institutional laundry programmes that demand consistent, fibre-safe results.",
    accentCta: "quote" as const,
    glow: "rgba(186, 220, 255, 0.22)",
  },
  {
    src: "/assets/images/hero/hero-slide-silkcare.webp",
    alt: "VESTRA Silk Care — silk and luxury garment wash",
    kicker: "WORLD-CLASS CLEANING SOLUTIONS",
    headline: "Professional Fabric Care",
    highlight: "Engineered for Delicate Textiles",
    description:
      "Silk Care is a pH-balanced wash for silk, satin, and luxury garments handled by hotels, valets, and specialist laundries.",
    accentCta: "buy" as const,
    glow: "rgba(46, 180, 120, 0.2)",
  },
  {
    src: "/assets/images/hero/hero-slide-ecosuit.webp",
    alt: "VESTRA Eco Suit Cleaner — respect the garment",
    kicker: "WORLD-CLASS CLEANING SOLUTIONS",
    headline: "Professional Garment Care",
    highlight: "Engineered for Tailored Fabrics",
    description:
      "Eco Suit Cleaner removes soil from suits and dark garments without fading or fabric damage — built for professional finishing rooms.",
    accentCta: "buy" as const,
    glow: "rgba(80, 140, 220, 0.22)",
  },
  {
    src: "/assets/images/hero/hero-slide-range.webp",
    alt: "VESTRA professional detergent range manufactured in Uganda",
    kicker: "WORLD-CLASS CLEANING SOLUTIONS",
    headline: "Professional Cleaning Solutions",
    highlight: "Engineered for Every Wash",
    description:
      "Powerful commercial detergents designed for businesses, institutions, and professionals who need reliable supply and consistent quality.",
    accentCta: "quote" as const,
    glow: "rgba(112, 192, 80, 0.18)",
  },
] as const;

const trustStrip = [
  { icon: "Hotel", label: "Hotels" },
  { icon: "Stethoscope", label: "Hospitals" },
  { icon: "Building2", label: "Laundry Services" },
  { icon: "Factory", label: "Factories" },
  { icon: "School", label: "Schools" },
  { icon: "Landmark", label: "Government" },
] as const;

const SLIDE_INTERVAL_MS = 5000;
const SLIDE_FADE_MS = 0.9;
const DESKTOP_TRANSITION_S = 0.55;

export function HeroSection() {
  const prefersReducedMotion = useReducedMotion();
  const [activeIndex, setActiveIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const active = heroSlides[activeIndex];

  useEffect(() => {
    if (prefersReducedMotion || heroSlides.length <= 1 || paused) return;

    const timer = window.setInterval(() => {
      setActiveIndex((current) => (current + 1) % heroSlides.length);
    }, SLIDE_INTERVAL_MS);

    return () => window.clearInterval(timer);
  }, [prefersReducedMotion, paused]);

  const goTo = (index: number) => {
    setActiveIndex((index + heroSlides.length) % heroSlides.length);
  };

  return (
    <section
      id="home"
      className="relative overflow-hidden bg-primary-900 pt-[calc(72px+env(safe-area-inset-top))]"
      aria-labelledby="hero-heading"
    >
      <h1 id="hero-heading" className="sr-only">
        Professional Cleaning Solutions. Engineered for Every Standard.
      </h1>
      <div className="pointer-events-none absolute inset-0 z-0" aria-hidden="true">
        <div
          className="absolute inset-0 opacity-50"
          style={{
            background:
              "radial-gradient(ellipse 70% 50% at 50% 0%, rgba(25,85,145,0.28) 0%, transparent 55%), radial-gradient(circle at 85% 70%, rgba(8,42,82,0.4) 0%, transparent 40%)",
          }}
        />
      </div>

      <MobileHero
        activeIndex={activeIndex}
        prefersReducedMotion={prefersReducedMotion}
        onSelect={goTo}
      />

      <DesktopHero
        activeIndex={activeIndex}
        prefersReducedMotion={prefersReducedMotion}
        paused={paused}
        onPausedChange={setPaused}
        onSelect={goTo}
      />

      <span className="sr-only" aria-live="polite">
        {active?.alt}
      </span>
    </section>
  );
}

function MobileHero({
  activeIndex,
  prefersReducedMotion,
  onSelect,
}: {
  activeIndex: number;
  prefersReducedMotion: boolean;
  onSelect: (index: number) => void;
}) {
  return (
    <div className="relative z-10 lg:hidden">
      <div className="relative flex w-full justify-center bg-[#031128]">
        <div className="relative w-full max-w-[28rem] sm:max-w-[32rem] md:max-w-[36rem]">
          <div className="relative aspect-[4/5] w-full overflow-hidden">
            <div className="absolute inset-0 grid">
              {heroSlides.map((slide, index) => (
                <motion.div
                  key={slide.src}
                  className="col-start-1 row-start-1 h-full w-full"
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
                    fill
                    priority={index === 0}
                    sizes="(max-width: 1023px) 100vw, 576px"
                    className="object-contain object-center"
                  />
                </motion.div>
              ))}
            </div>

            {!prefersReducedMotion && (
              <div
                className="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 sm:bottom-4"
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
                    onClick={() => onSelect(index)}
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
      </div>

      <Container className="relative z-10 pb-16 pt-8 sm:pb-20 sm:pt-10">
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

          <p className="mb-6 text-4xl font-black leading-[1.08] tracking-tight sm:text-5xl">
            Professional Cleaning Solutions.
            <br />
            <span className="text-secondary-500">Engineered for Every Standard.</span>
          </p>

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
    </div>
  );
}

function DesktopHero({
  activeIndex,
  prefersReducedMotion,
  paused,
  onPausedChange,
  onSelect,
}: {
  activeIndex: number;
  prefersReducedMotion: boolean;
  paused: boolean;
  onPausedChange: (value: boolean) => void;
  onSelect: (index: number) => void;
}) {
  const active = heroSlides[activeIndex];
  const duration = prefersReducedMotion ? 0 : DESKTOP_TRANSITION_S;

  const onKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
    if (event.key === "ArrowLeft") {
      event.preventDefault();
      onSelect(activeIndex - 1);
    }
    if (event.key === "ArrowRight") {
      event.preventDefault();
      onSelect(activeIndex + 1);
    }
  };

  return (
    <div className="relative z-10 hidden lg:block">
      <div
        className="relative grid min-h-[calc(100dvh-72px)] grid-cols-[minmax(0,3fr)_minmax(0,2fr)]"
        role="region"
        aria-roledescription="carousel"
        aria-label="Homepage product slides"
        tabIndex={0}
        onMouseEnter={() => onPausedChange(true)}
        onMouseLeave={() => onPausedChange(false)}
        onFocusCapture={() => onPausedChange(true)}
        onBlurCapture={(event) => {
          if (!event.currentTarget.contains(event.relatedTarget as Node | null)) {
            onPausedChange(false);
          }
        }}
        onKeyDown={onKeyDown}
      >
        <div
          className="pointer-events-none absolute inset-0 transition-colors duration-500"
          aria-hidden="true"
          style={{
            background: `radial-gradient(ellipse 55% 70% at 78% 48%, ${active.glow} 0%, transparent 58%), radial-gradient(circle at 12% 18%, rgba(25,85,145,0.35) 0%, transparent 42%), linear-gradient(180deg, rgba(3,17,40,0.2) 0%, rgba(3,17,40,0.55) 100%)`,
          }}
        />
        <div
          className="pointer-events-none absolute inset-0 opacity-40"
          aria-hidden="true"
          style={{
            backgroundImage:
              "radial-gradient(circle, rgba(255,255,255,0.18) 0 1px, transparent 1.5px)",
            backgroundSize: "72px 72px",
            maskImage: "radial-gradient(circle at 78% 50%, black 0%, transparent 62%)",
          }}
        />

        <div className="relative z-10 flex items-center py-12 pl-[max(2rem,calc((100vw-1440px)/2+2rem))] pr-10 xl:pr-14">
          <div className="w-full max-w-[760px] text-white">
            <AnimatePresence mode="wait">
              <motion.div
                key={active.src}
                initial={prefersReducedMotion ? false : { opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                exit={prefersReducedMotion ? undefined : { opacity: 0, y: -12 }}
                transition={{ duration, ease: "easeOut" }}
              >
                <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-4 py-2 text-sm font-bold tracking-wider backdrop-blur-sm">
                  <Star className="h-4 w-4 flex-shrink-0 fill-secondary-500 text-secondary-500" aria-hidden="true" />
                  <span>{active.kicker}</span>
                </div>

                <p className="mb-5 text-[clamp(2.4rem,4.2vw,4.15rem)] font-black leading-[1.05] tracking-tight">
                  {active.headline}
                  <br />
                  <span className="text-secondary-500">{active.highlight}</span>
                </p>

                <p className="mb-9 max-w-xl text-lg leading-relaxed text-white/80">
                  {active.description}
                </p>
              </motion.div>
            </AnimatePresence>

            <div className="mb-10 flex flex-wrap items-center gap-3">
              <HeroCta
                href="/request-quote"
                track="hero-primary-cta"
                label="Request a Quote"
                variant="gradient"
                emphasized={active.accentCta === "quote"}
              />
              <HeroCta
                href="/where-to-buy"
                track="hero-where-to-buy-cta"
                label="Where to Buy"
                variant="outline"
                emphasized={active.accentCta === "buy"}
              />
              <HeroCta
                href="/distributor"
                track="hero-secondary-cta"
                label="Become a Distributor"
                variant="outline"
                emphasized={active.accentCta === "distributor"}
              />
            </div>

            <dl className="grid max-w-3xl grid-cols-4 gap-4 border-t border-white/15 pt-8">
              <StatCell
                value={<IndustryCount reduced={prefersReducedMotion} />}
                label="Industries"
              />
              <StatCell value="Nationwide" label="Distribution" />
              <StatCell value="Uganda" label="Manufactured" />
              <StatCell value="Premium" label="Quality Standards" />
            </dl>
          </div>
        </div>

        <div className="relative min-h-full overflow-hidden">
          <AnimatePresence initial={false}>
            <motion.div
              key={active.src}
              className="absolute inset-0"
              initial={prefersReducedMotion ? false : { opacity: 0, scale: 1.06, x: 24 }}
              animate={{ opacity: 1, scale: 1, x: 0 }}
              exit={prefersReducedMotion ? undefined : { opacity: 0, scale: 1.02, x: -12 }}
              transition={{ duration, ease: [0.22, 1, 0.36, 1] }}
            >
              <Image
                src={active.src}
                alt={active.alt}
                fill
                priority={activeIndex === 0}
                sizes="(min-width: 1024px) 40vw, 100vw"
                className="object-cover object-center"
              />
            </motion.div>
          </AnimatePresence>

          <div
            className="pointer-events-none absolute inset-y-0 left-0 w-28 bg-gradient-to-r from-primary-900 via-primary-900/55 to-transparent"
            aria-hidden="true"
          />
          <div
            className="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#031128]/35 via-transparent to-[#031128]/20"
            aria-hidden="true"
          />

          <div className="absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 items-center gap-3">
            <button
              type="button"
              aria-label="Previous slide"
              onClick={() => onSelect(activeIndex - 1)}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 bg-black/25 text-white backdrop-blur-sm transition hover:bg-black/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-500"
            >
              <ChevronLeft className="h-5 w-5" aria-hidden="true" />
            </button>
            <div className="flex items-center gap-2" role="tablist" aria-label="Hero image slides">
              {heroSlides.map((slide, index) => (
                <button
                  key={slide.src}
                  type="button"
                  role="tab"
                  aria-selected={index === activeIndex}
                  aria-label={`Show slide ${index + 1}`}
                  onClick={() => onSelect(index)}
                  className={cn(
                    "h-1.5 rounded-full transition-all duration-300",
                    index === activeIndex
                      ? "w-8 bg-secondary-500"
                      : "w-2 bg-white/45 hover:bg-white/75"
                  )}
                />
              ))}
            </div>
            <button
              type="button"
              aria-label="Next slide"
              onClick={() => onSelect(activeIndex + 1)}
              className="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 bg-black/25 text-white backdrop-blur-sm transition hover:bg-black/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-500"
            >
              <ChevronRight className="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
          <span className="sr-only">{paused ? "Slideshow paused" : "Slideshow playing"}</span>
        </div>
      </div>

      <div className="relative z-10 border-t border-white/10 bg-[#02101f]">
        <div className="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-4 px-8 py-4">
          <p className="text-xs font-bold uppercase tracking-[0.22em] text-white/45">Trusted by</p>
          <ul className="flex flex-wrap items-center gap-x-8 gap-y-3">
            {trustStrip.map((item) => (
              <li key={item.label} className="flex items-center gap-2 text-sm font-medium text-white/80">
                <Icon name={item.icon} className="h-4 w-4 text-secondary-500" aria-hidden="true" />
                {item.label}
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}

function HeroCta({
  href,
  track,
  label,
  variant,
  emphasized,
}: {
  href: string;
  track: string;
  label: string;
  variant: "gradient" | "outline";
  emphasized: boolean;
}) {
  return (
    <Button
      asChild
      variant={variant}
      className={cn(
        "h-auto justify-center rounded-full px-7 py-3.5 group",
        variant === "outline" &&
          "border-white/40 bg-white/10 text-white backdrop-blur-sm hover:border-white/50 hover:bg-white/20 hover:text-white",
        emphasized && "ring-2 ring-secondary-500 ring-offset-2 ring-offset-primary-900"
      )}
      rightIcon={<ChevronRight className="h-4 w-4 transition-transform-base group-hover:translate-x-1" aria-hidden="true" />}
    >
      <Link href={href} data-track={track}>
        {label}
      </Link>
    </Button>
  );
}

function StatCell({ value, label }: { value: ReactNode; label: string }) {
  return (
    <div>
      <dt className="sr-only">{label}</dt>
      <dd>
        <p className="text-xl font-black tracking-tight text-white xl:text-2xl">{value}</p>
        <p className="mt-1 text-xs font-medium uppercase tracking-wider text-white/55">{label}</p>
      </dd>
    </div>
  );
}

function IndustryCount({ reduced }: { reduced: boolean }) {
  const [count, setCount] = useState(reduced ? 10 : 0);
  const ref = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    if (reduced) {
      setCount(10);
      return;
    }

    const node = ref.current;
    if (!node) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry?.isIntersecting) return;
        const started = performance.now();
        const duration = 650;
        const tick = (now: number) => {
          const progress = Math.min(1, (now - started) / duration);
          setCount(Math.round(10 * progress));
          if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        observer.disconnect();
      },
      { threshold: 0.4 }
    );

    observer.observe(node);
    return () => observer.disconnect();
  }, [reduced]);

  return <span ref={ref}>{count}+</span>;
}
