"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ChevronRight, Star } from "lucide-react";
import { Container } from "@/components/common/container";
import { Icon } from "@/components/common/icon";

const heroFeatures = [
  { icon: "Shield", title: "Professional", description: "Results" },
  { icon: "Leaf", title: "Fabric", description: "Protection" },
  { icon: "FlaskConical", title: "Innovative", description: "Formulations" },
];

export function HeroSection() {
  return (
    <section
      id="home"
      className="relative min-h-[600px] lg:min-h-[calc(100vh-88px)] flex items-center bg-[#031128] overflow-hidden pt-28 lg:pt-0"
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
            initial={{ opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="text-white max-w-xl"
          >
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/20 bg-white/5 backdrop-blur-sm text-xs sm:text-sm font-bold tracking-wider mb-6">
              <Star className="w-4 h-4 text-green-500 fill-green-500" />
              <span>PROFESSIONAL CARE. PREMIUM RESULTS.</span>
            </div>

            <h1 className="text-4xl sm:text-5xl lg:text-[clamp(3rem,6vw,6rem)] font-black leading-[1.05] tracking-tight mb-6">
              Professional
              <br />
              Fabric Care.
              <br />
              <span className="text-green-500">
                Engineered
                <br />
                for Excellence.
              </span>
            </h1>

            <p className="text-white/80 text-base sm:text-lg leading-relaxed mb-8 max-w-xl">
              VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning
              solutions that combine advanced chemistry, innovation, and exceptional garment care.
            </p>

            <div className="flex flex-wrap gap-4 mb-10">
              <Link
                href="/products"
                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-transform group"
              >
                Explore Products
                <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </Link>
              <Link
                href="/distributor"
                className="inline-flex items-center gap-2 px-6 py-3.5 rounded-full font-semibold text-white border border-white/40 bg-white/10 backdrop-blur-sm hover:bg-white/20 hover:-translate-y-1 transition-all group"
              >
                Become a Distributor
                <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </Link>
            </div>

            <div className="flex flex-wrap items-center gap-4 sm:gap-6">
              {heroFeatures.map((feature, index) => (
                <div key={feature.title} className="flex items-center gap-3">
                  <div className="w-10 h-10 sm:w-12 sm:h-12 rounded-full border border-green-500/40 bg-green-500/10 flex items-center justify-center text-green-500">
                    <Icon name={feature.icon} className="w-5 h-5" />
                  </div>
                  <div className="leading-tight">
                    <strong className="block text-white text-sm font-semibold">{feature.title}</strong>
                    <span className="text-white/70 text-xs sm:text-sm">{feature.description}</span>
                  </div>
                  {index < heroFeatures.length - 1 && (
                    <div className="hidden sm:block w-px h-8 bg-white/20 ml-2" />
                  )}
                </div>
              ))}
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.9, ease: "easeOut", delay: 0.2 }}
            className="relative lg:absolute lg:right-0 lg:bottom-0 lg:w-[55%] lg:h-full flex items-end justify-end"
          >
            <div className="relative w-full aspect-[4/3] lg:aspect-auto lg:h-full">
              <Image
                src="/assets/images/hero/home-page-image.png"
                alt="VESTRA Professional Fabric Care Product Composition"
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
