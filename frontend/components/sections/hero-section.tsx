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
      className="relative min-h-[600px] lg:min-h-[calc(100vh-88px)] flex items-center bg-primary-900 overflow-hidden pt-28 lg:pt-0"
      aria-labelledby="hero-heading"
    >
      {/* Background effects */}
      <div className="absolute inset-0 z-0">
        <div
          className="absolute inset-0 opacity-40"
          style={{
            background:
              "radial-gradient(ellipse 80% 70% at 85% 65%, rgba(8,42,82,0.35) 0%, transparent 55%), radial-gradient(circle at 20% 30%, rgba(25,85,145,0.08) 0%, transparent 30%)",
          }}
        />
        <div className="absolute -top-40 right-0 w-[clamp(360px,40vw,680px)] h-[clamp(360px,40vw,680px)] rounded-full bg-[rgba(8,45,90,0.6)] blur-[clamp(50px,6vw,100px)] opacity-85" />
        <div className="absolute -bottom-24 right-[30%] w-[clamp(280px,30vw,480px)] h-[clamp(280px,30vw,480px)] rounded-full bg-[rgba(20,90,160,0.2)] blur-[clamp(50px,6vw,100px)] opacity-70" />
        <div
          className="absolute inset-0"
          style={{
            background:
              "radial-gradient(circle at 78% 58%, rgba(255,255,255,0.05) 0%, transparent 45%)",
          }}
        />
        <div
          className="absolute inset-0"
          style={{
            background:
              "radial-gradient(ellipse at center, transparent 45%, rgba(2,8,18,0.75) 100%)",
          }}
        />
      </div>

      <Container className="relative z-10 w-full py-16 lg:py-24">
        <div className="grid lg:grid-cols-[45%_55%] gap-8 lg:gap-12 items-center">
          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="text-white max-w-full sm:max-w-xl min-w-0"
          >
            <div className="inline-flex max-w-full items-center gap-2 px-3 sm:px-4 py-2 rounded-full border border-white/20 bg-white/5 backdrop-blur-sm text-[11px] sm:text-sm font-bold tracking-wider mb-6">
              <Star className="w-4 h-4 text-secondary-500 fill-secondary-500 flex-shrink-0" aria-hidden="true" />
              <span className="truncate sm:whitespace-normal">PROFESSIONAL CLEANING SOLUTIONS</span>
            </div>

            <h1
              id="hero-heading"
              className="text-4xl sm:text-5xl lg:text-[clamp(3rem,6vw,6rem)] font-black leading-[1.05] tracking-tight mb-6"
            >
              Professional
              <br />
              Cleaning Solutions
              <br />
              <span className="text-secondary-500">
                Manufactured
                <br />
                for Uganda.
              </span>
            </h1>

            <p className="text-white/80 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
              VESTRA® manufactures high-performance detergents and fabric care products for
              businesses, institutions, and distribution partners who demand consistent quality,
              reliable supply, and professional results.
            </p>

            <div className="flex flex-wrap gap-4 mb-10">
              <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto group" rightIcon={<ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
              <Link href="/request-quote" data-track="hero-primary-cta">Request a Quote</Link>
            </Button>
              <Button asChild variant="outline" className="rounded-full px-6 py-3.5 h-auto border-white/40 text-white bg-white/10 backdrop-blur-sm hover:bg-white/20 hover:text-white hover:border-white/50" rightIcon={<ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />}>
                <Link href="/distributor" data-track="hero-secondary-cta">Become a Distributor</Link>
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

          <motion.div
            initial={prefersReducedMotion ? { opacity: 1, scale: 1 } : { opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.9, ease: "easeOut", delay: 0.2 }}
            className="relative lg:absolute lg:right-0 lg:bottom-0 lg:w-[55%] lg:h-full flex items-end justify-end"
          >
            <div className="relative w-full aspect-[4/3] lg:aspect-auto lg:h-full">
              <Image
                src="/assets/images/hero/home-page-image.webp"
                alt="VESTRA professional detergent product range manufactured in Uganda"
                fill
                sizes="(max-width: 1024px) 100vw, 55vw"
                priority
                className="object-contain object-right-bottom"
                style={{
                  maskImage:
                    "linear-gradient(to right, transparent 0%, black 18%, black 92%, transparent 100%)",
                  WebkitMaskImage:
                    "linear-gradient(to right, transparent 0%, black 18%, black 92%, transparent 100%)",
                }}
              />
              <div
                className="absolute inset-0 pointer-events-none"
                style={{
                  background:
                    "radial-gradient(ellipse 100% 80% at 75% 75%, transparent 55%, rgba(3,17,40,0.5) 85%, rgba(3,17,40,0.9) 100%)",
                }}
              />
            </div>
          </motion.div>
        </div>
      </Container>
    </section>
  );
}
