"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, CheckCircle2, Leaf } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";
import { MissionVisionCard } from "@/components/common/mission-vision-card";
import { Icon } from "@/components/common/icon";
import { useCompanyInfo } from "@/hooks/use-settings";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";

const coreValues = [
  { icon: "ShieldCheck", title: "Quality", description: "Rigorous standards in every batch we produce." },
  { icon: "BadgeCheck", title: "Integrity", description: "Honest, transparent partnerships with every customer." },
  { icon: "FlaskConical", title: "Innovation", description: "Continuous improvement in formulation and process." },
  { icon: "HeartHandshake", title: "Customer Success", description: "Your growth and satisfaction drive our work." },
  { icon: "Truck", title: "Reliability", description: "Consistent supply you can plan your business around." },
  { icon: "Leaf", title: "Sustainability", description: "Responsible manufacturing for a better future." },
];

const manufacturedCategories = [
  { icon: "Droplets", title: "Laundry Detergents", description: "High-performance formulas for commercial and institutional laundering." },
  { icon: "Sparkles", title: "Fabric Softeners", description: "Care solutions that keep fabrics fresh and comfortable." },
  { icon: "Home", title: "Household Cleaning Products", description: "Effective cleaning products designed for everyday use." },
  { icon: "Building2", title: "Commercial Cleaning Products", description: "Professional-grade products for facilities and service teams." },
  { icon: "Factory", title: "Industrial Solutions", description: "Heavy-duty cleaning formulations for demanding environments." },
];

const businessStrengths = [
  { icon: "Award", title: "Premium Product Quality", description: "Manufactured to professional standards with strict quality control." },
  { icon: "Factory", title: "Reliable Manufacturing", description: "Modern production processes built for consistency and scale." },
  { icon: "Truck", title: "Consistent Supply", description: "Dependable delivery schedules that keep your operations running." },
  { icon: "TrendingUp", title: "Competitive Bulk Pricing", description: "Attractive commercial pricing for volume buyers and distributors." },
  { icon: "Users", title: "Professional Support", description: "Dedicated sales and technical assistance for every partner." },
  { icon: "Globe", title: "Nationwide Distribution", description: "Supply capability across Uganda through our distribution network." },
];

const industries = [
  { icon: "Hotel", title: "Hotels" },
  { icon: "Stethoscope", title: "Hospitals" },
  { icon: "School", title: "Schools" },
  { icon: "HeartHandshake", title: "NGOs" },
  { icon: "Landmark", title: "Government" },
  { icon: "Building2", title: "Commercial Laundries" },
  { icon: "Factory", title: "Manufacturers" },
  { icon: "Briefcase", title: "Cleaning Companies" },
  { icon: "ShoppingCart", title: "Retail & Wholesale" },
];

const qualityIndicators = [
  "Stringent raw material checks",
  "Controlled batch production",
  "Finished product testing",
  "Traceable supply chain records",
];

const sustainabilityPoints = [
  "Efficient production processes that reduce waste",
  "Responsible sourcing of raw materials",
  "Continuous review of formulation environmental impact",
  "Commitment to safe manufacturing practices",
];

const prefersReducedMotion =
  typeof window !== "undefined" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export function AboutPageClient() {
  const { companyInfo, isLoading } = useCompanyInfo();

  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestradetergents.com/" },
          { name: "About Us", url: "https://vestradetergents.com/about" },
        ])}
      />
      <main>
        <PageHero
          title="About VESTRA®"
          subtitle="A Ugandan manufacturer committed to producing professional cleaning solutions that businesses, institutions, and distributors can rely on."
          breadcrumb={[{ label: "About Us" }]}
        />

        {/* Our Story */}
        <section id="story" className="py-24 lg:py-36 bg-white" aria-labelledby="story-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
              <motion.div
                initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: -40 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.7 }}
                className="relative aspect-[4/3] rounded-[24px] overflow-hidden bg-primary-50 shadow-xl"
              >
                <Image
                  src="/assets/images/hero/home-page-image.webp"
                  alt="VESTRA professional cleaning products manufactured in Uganda"
                  fill
                  sizes="(max-width: 1024px) 100vw, 50vw"
                  className="object-contain p-6 lg:p-10"
                />
              </motion.div>

              <motion.div
                initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: 40 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.7, delay: 0.1 }}
              >
                <SectionHeader
                  id="story-heading"
                  title="Our Story"
                  subtitle="Built on a belief that Uganda deserves world-class cleaning products manufactured locally."
                  centered={false}
                  className="mb-8"
                />
                {isLoading ? (
                  <div className="space-y-4">
                    <div className="h-5 w-full rounded bg-neutral-200 animate-pulse" />
                    <div className="h-5 w-5/6 rounded bg-neutral-200 animate-pulse" />
                    <div className="h-5 w-4/6 rounded bg-neutral-200 animate-pulse" />
                  </div>
                ) : (
                  <>
                    <p className="text-body text-base lg:text-lg leading-relaxed mb-5">
                      Founded in {companyInfo?.founded || "2020"} and headquartered in{" "}
                      {companyInfo?.headquarters || "Kampala, Uganda"}, VESTRA® was created to meet
                      the growing demand for reliable, professional-grade cleaning solutions across
                      Uganda.
                    </p>
                    <p className="text-body text-base lg:text-lg leading-relaxed mb-5">
                      We combine advanced chemistry with practical manufacturing expertise to produce
                      detergents and fabric care products that clean powerfully while protecting the
                      fabrics and surfaces our partners depend on.
                    </p>
                    <p className="text-body text-base lg:text-lg leading-relaxed">
                      Our long-term vision is to become one of East Africa&apos;s most trusted names
                      in professional cleaning — known for quality, consistency, and partnership.
                    </p>
                  </>
                )}
              </motion.div>
            </div>
          </Container>
        </section>

        {/* Mission, Vision & Values */}
        <section id="mission-vision" className="py-24 lg:py-36 bg-surface-page" aria-labelledby="mission-heading">
          <Container>
            <SectionHeader
              id="mission-heading"
              title="Mission, Vision & Values"
              subtitle="The purpose and principles that guide every product we make and every partnership we build."
              className="mb-12"
            />

            <div className="grid md:grid-cols-2 gap-6 lg:gap-8 mb-12">
              <MissionVisionCard
                icon="Target"
                label="Our Mission"
                title="Purpose-Driven Manufacturing"
                description={
                  companyInfo?.mission ||
                  "To manufacture professional cleaning solutions that help businesses and institutions across Uganda operate with confidence."
                }
              />
              <MissionVisionCard
                icon="Eye"
                label="Our Vision"
                title="A Trusted East African Brand"
                description={
                  companyInfo?.vision ||
                  "To be recognised as one of East Africa's most trusted manufacturers of professional cleaning and fabric care products."
                }
              />
            </div>

            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {coreValues.map((value, index) => (
                <ValueCard
                  key={value.title}
                  icon={value.icon}
                  title={value.title}
                  description={value.description}
                  index={index}
                />
              ))}
            </div>
          </Container>
        </section>

        {/* What We Manufacture */}
        <section id="manufactured" className="py-24 lg:py-36 bg-white" aria-labelledby="manufactured-heading">
          <Container>
            <SectionHeader
              id="manufactured-heading"
              title="What We Manufacture"
              subtitle="A focused range of professional cleaning and fabric care products for business, institutional, and household use."
            />

            <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
              {manufacturedCategories.map((category, index) => (
                <motion.div
                  key={category.title}
                  initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.6, delay: index * 0.08 }}
                  className="text-center p-6 lg:p-7 rounded-[20px] bg-surface-card border border-default shadow-sm hover:-translate-y-1.5 hover:shadow-md hover:border-primary-300/50 transition-all-base"
                >
                  <div className="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-400 flex items-center justify-center text-white mx-auto mb-5 shadow-lg shadow-primary-400/25">
                    <Icon name={category.icon} className="w-7 h-7" aria-hidden="true" />
                  </div>
                  <h3 className="text-base lg:text-lg font-bold text-primary-900 mb-2">{category.title}</h3>
                  <p className="text-sm text-muted leading-relaxed">{category.description}</p>
                </motion.div>
              ))}
            </div>
          </Container>
        </section>

        {/* Why Businesses Choose VESTRA® */}
        <section id="why-partners" className="py-24 lg:py-36 bg-primary-900" aria-labelledby="why-partners-heading">
          <Container>
            <SectionHeader
              id="why-partners-heading"
              title="Why Businesses Choose VESTRA®"
              subtitle="The strengths that make VESTRA® a dependable partner for organisations across Uganda."
              light
            />

            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {businessStrengths.map((strength, index) => (
                <motion.div
                  key={strength.title}
                  initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.6, delay: index * 0.08 }}
                  className="text-center text-white p-6 lg:p-7 rounded-[20px] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 hover:-translate-y-1.5 hover:border-secondary-500/40 transition-all-base"
                >
                  <div className="w-16 h-16 rounded-full border-2 border-white/25 flex items-center justify-center mx-auto mb-5 text-secondary-500">
                    <Icon name={strength.icon} className="w-7 h-7" aria-hidden="true" />
                  </div>
                  <h3 className="text-base lg:text-lg font-semibold mb-2">{strength.title}</h3>
                  <p className="text-sm lg:text-base text-white/70 leading-relaxed">{strength.description}</p>
                </motion.div>
              ))}
            </div>
          </Container>
        </section>

        {/* Industries We Serve */}
        <section id="industries" className="py-24 lg:py-36 bg-surface-page" aria-labelledby="industries-heading">
          <Container>
            <SectionHeader
              id="industries-heading"
              title="Industries We Serve"
              subtitle="VESTRA® supplies organisations across multiple sectors with dependable cleaning solutions."
            />

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6">
              {industries.map((industry, index) => (
                <motion.div
                  key={industry.title}
                  initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.5, delay: index * 0.05 }}
                  className="text-center p-5 lg:p-6 rounded-[20px] bg-white border border-default shadow-sm hover:-translate-y-1.5 hover:shadow-md hover:border-primary-300/50 transition-all-base"
                >
                  <div className="w-12 h-12 lg:w-14 lg:h-14 rounded-full bg-primary-50 flex items-center justify-center text-primary-500 mx-auto mb-4">
                    <Icon name={industry.icon} className="w-6 h-6 lg:w-7 lg:h-7" aria-hidden="true" />
                  </div>
                  <h3 className="text-sm lg:text-base font-bold text-primary-900">{industry.title}</h3>
                </motion.div>
              ))}
            </div>
          </Container>
        </section>

        {/* Quality Commitment */}
        <section id="quality" className="py-24 lg:py-36 bg-white" aria-labelledby="quality-heading">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
              <motion.div
                initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: -40 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.7 }}
              >
                <SectionHeader
                  id="quality-heading"
                  title="Our Quality Commitment"
                  subtitle="Quality is not a single step — it is built into every stage of our manufacturing process."
                  centered={false}
                  className="mb-8"
                />
                <ul className="space-y-4 mb-8">
                  {qualityIndicators.map((item) => (
                    <li key={item} className="flex items-start gap-3">
                      <CheckCircle2 className="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                      <span className="text-body leading-relaxed">{item}</span>
                    </li>
                  ))}
                </ul>
                <p className="text-body leading-relaxed">
                  This disciplined approach helps us deliver consistent products that our partners
                  can specify with confidence.
                </p>
              </motion.div>

              <motion.div
                initial={prefersReducedMotion ? { opacity: 1, x: 0 } : { opacity: 0, x: 40 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true, margin: "-100px" }}
                transition={{ duration: 0.7, delay: 0.1 }}
                className="grid grid-cols-2 gap-4"
              >
                <div className="p-6 rounded-[20px] bg-primary-900 text-white text-center">
                  <p className="text-3xl lg:text-4xl font-black text-secondary-500 mb-1">100%</p>
                  <p className="text-sm text-white/80">Batch tested</p>
                </div>
                <div className="p-6 rounded-[20px] bg-secondary-500 text-white text-center">
                  <p className="text-3xl lg:text-4xl font-black text-white mb-1">UG</p>
                  <p className="text-sm text-white/90">Manufactured</p>
                </div>
                <div className="p-6 rounded-[20px] bg-primary-50 text-primary-900 text-center">
                  <p className="text-3xl lg:text-4xl font-black text-primary-500 mb-1">B2B</p>
                  <p className="text-sm text-primary-700">Focused</p>
                </div>
                <div className="p-6 rounded-[20px] bg-surface-card border border-default text-center">
                  <p className="text-3xl lg:text-4xl font-black text-secondary-600 mb-1">24h</p>
                  <p className="text-sm text-muted">Quote response</p>
                </div>
              </motion.div>
            </div>
          </Container>
        </section>

        {/* Sustainability */}
        <section id="sustainability" className="py-24 lg:py-36 bg-surface-page" aria-labelledby="sustainability-heading">
          <Container>
            <div className="max-w-3xl mx-auto text-center">
              <SectionHeader
                id="sustainability-heading"
                title="Responsible Manufacturing"
                subtitle="We take a practical approach to sustainability, focusing on areas where we can make a meaningful difference."
              />
            </div>

            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
              {sustainabilityPoints.map((point, index) => (
                <motion.div
                  key={point}
                  initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 30 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, margin: "-100px" }}
                  transition={{ duration: 0.5, delay: index * 0.08 }}
                  className="flex items-start gap-3 p-6 rounded-[20px] bg-white border border-default shadow-sm"
                >
                  <Leaf className="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <span className="text-body text-sm lg:text-base leading-relaxed">{point}</span>
                </motion.div>
              ))}
            </div>
          </Container>
        </section>

        {/* Partner CTA */}
        <section className="py-20 lg:py-28 bg-white" aria-labelledby="partner-heading">
          <Container>
            <motion.div
              initial={prefersReducedMotion ? { opacity: 1, y: 0 } : { opacity: 0, y: 40 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, margin: "-100px" }}
              transition={{ duration: 0.7 }}
              className="max-w-3xl mx-auto text-center px-6 py-12 lg:px-12 lg:py-16 rounded-[28px] border border-default shadow-lg bg-surface-card"
            >
              <h2
                id="partner-heading"
                className="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-primary-900 mb-4 tracking-tight"
              >
                Partner with VESTRA®
              </h2>
              <p className="text-base lg:text-lg text-muted mb-8 leading-relaxed">
                Whether you need a tailored quotation, distribution partnership, or commercial supply
                agreement, our team is ready to talk.
              </p>
              <div className="flex flex-wrap justify-center gap-4">
                <Link
                  href="/request-quote"
                  data-track="about-partner-quote"
                  className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-secondary-500 to-secondary-600 shadow-lg shadow-secondary-500/30 hover:-translate-y-1 transition-transform-base group"
                >
                  Request a Quote
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform-base" aria-hidden="true" />
                </Link>
                <Link
                  href="/distributor"
                  data-track="about-partner-distributor"
                  className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-primary-900 bg-white border border-default hover:bg-surface-page hover:-translate-y-1 transition-all-base"
                >
                  Become a Distributor
                </Link>
                <Link
                  href="/contact"
                  data-track="about-partner-contact"
                  className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-primary-900 hover:text-secondary-600 transition-colors-base"
                >
                  Contact Sales
                </Link>
              </div>
            </motion.div>
          </Container>
        </section>
      </main>
    </>
  );
}
