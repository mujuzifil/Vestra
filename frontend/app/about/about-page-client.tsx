"use client";

import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";
import { MissionVisionCard } from "@/components/common/mission-vision-card";
import { CTASection } from "@/components/common/cta-section";
import { PromiseSection } from "@/components/sections/promise-section";
import { ApiError } from "@/components/ui/api-error";
import { useCompanyInfo } from "@/hooks/use-settings";
import { JsonLd, breadcrumbSchema } from "@/lib/structured-data";

const coreValues = [
  {
    icon: "FlaskConical",
    title: "Innovation",
    description: "We continuously research and develop better formulas for modern fabric care.",
  },
  {
    icon: "ShieldCheck",
    title: "Quality",
    description: "Every product meets strict quality standards for professional results.",
  },
  {
    icon: "Leaf",
    title: "Responsibility",
    description: "We create effective solutions while caring for people and the environment.",
  },
  {
    icon: "Users",
    title: "Partnership",
    description: "We build lasting relationships with distributors, laundries, and customers.",
  },
];

export function AboutPageClient() {
  const { companyInfo, isLoading, error } = useCompanyInfo();

  return (
    <>
      <JsonLd
        data={breadcrumbSchema([
          { name: "Home", url: "https://vestra.com/" },
          { name: "About Us", url: "https://vestra.com/about" },
        ])}
      />
      <main>
        <PageHero
          title="About VESTRA"
          subtitle={companyInfo?.description || "VESTRA is a premium fabric care brand dedicated to developing high-performance cleaning solutions."}
          breadcrumb={[{ label: "About Us" }]}
        />

        {/* Company Introduction */}
        <section className="py-20 lg:py-28 bg-white">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
              <div className="relative aspect-[4/3] rounded-[24px] bg-gradient-to-br from-[#0d3b66] to-[#0a1628] overflow-hidden flex items-center justify-center p-8">
                <div
                  className="absolute inset-0 pointer-events-none"
                  style={{
                    background:
                      "radial-gradient(circle at 30% 30%, rgba(112,192,80,0.15) 0%, transparent 50%)",
                  }}
                />
                <h2 className="relative z-10 text-4xl lg:text-6xl font-black text-white tracking-tight">
                  {companyInfo?.name || "VESTRA"}
                </h2>
              </div>
              <div>
                <span className="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-green-500/10 text-green-600 mb-4">
                  Company Introduction
                </span>
                <h2 className="text-2xl lg:text-4xl font-extrabold text-[#0a1628] mb-6 tracking-tight">
                  Professional Fabric Care,
                  <br />
                  <span className="text-green-600">Engineered for Excellence</span>
                </h2>
                {isLoading ? (
                  <div className="space-y-4">
                    <div className="h-5 w-full rounded bg-[#e2e8f0] animate-pulse" />
                    <div className="h-5 w-5/6 rounded bg-[#e2e8f0] animate-pulse" />
                  </div>
                ) : error ? (
                  <ApiError onRetry={() => window.location.reload()} />
                ) : (
                  <>
                    <p className="text-[#475569] text-base lg:text-lg leading-relaxed mb-5">
                      Founded in {companyInfo?.founded || "2020"} and headquartered in {companyInfo?.headquarters || "Kampala, Uganda"},
                      VESTRA was created to meet the growing demand for professional-grade fabric care
                      solutions across Africa.
                    </p>
                    <p className="text-[#475569] text-base lg:text-lg leading-relaxed">
                      We combine advanced chemistry with practical garment care knowledge to deliver
                      products that clean powerfully while preserving the fabrics people value most.
                    </p>
                  </>
                )}
              </div>
            </div>
          </Container>
        </section>

        {/* Mission & Vision */}
        <section className="py-20 lg:py-28 bg-[#f8fafc]">
          <Container>
            <div className="grid md:grid-cols-2 gap-6 lg:gap-8">
              {isLoading ? (
                <>
                  <div className="h-64 rounded-[20px] bg-[#e2e8f0] animate-pulse" />
                  <div className="h-64 rounded-[20px] bg-[#e2e8f0] animate-pulse" />
                </>
              ) : (
                <>
                  <MissionVisionCard
                    icon="Target"
                    label="Our Mission"
                    title="Purpose-Driven Care"
                    description={companyInfo?.mission || "To deliver professional-grade fabric care solutions."}
                  />
                  <MissionVisionCard
                    icon="Eye"
                    label="Our Vision"
                    title="A Respected African Brand"
                    description={companyInfo?.vision || "Building one of Africa's most respected fabric care brands."}
                  />
                </>
              )}
            </div>
          </Container>
        </section>

        {/* Core Values */}
        <section className="py-20 lg:py-28 bg-white">
          <Container>
            <SectionHeader title="Core Values" subtitle="The principles that guide everything we do." />
            <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
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

        {/* Brand Philosophy */}
        <section className="py-20 lg:py-28 bg-[#0a1628] text-white">
          <Container>
            <div className="max-w-4xl mx-auto text-center">
              <span className="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-green-400 mb-6">
                Brand Philosophy
              </span>
              <h2 className="text-2xl lg:text-4xl font-extrabold mb-6 tracking-tight">
                More Than Cleaning
              </h2>
              <p className="text-lg lg:text-xl text-white/80 leading-relaxed">
                {companyInfo?.philosophy || "We believe fabric care should do more than clean. It should preserve, protect, and prolong the life of every garment we touch."}
              </p>
            </div>
          </Container>
        </section>

        {/* Promise Section */}
        <PromiseSection />

        {/* Looking Ahead */}
        <section className="py-20 lg:py-28 bg-white">
          <Container>
            <div className="grid lg:grid-cols-2 gap-12 items-center">
              <div>
                <SectionHeader
                  title="Looking Ahead"
                  subtitle="Our roadmap for growth and innovation across Africa."
                  centered={false}
                />
                <p className="text-[#475569] text-base lg:text-lg leading-relaxed mb-6">
                  As we grow, VESTRA remains committed to expanding our product line, strengthening
                  our distribution network, and building partnerships that bring premium fabric care
                  to more homes and businesses.
                </p>
                <ul className="space-y-3">
                  {[
                    "Launch new specialty care products",
                    "Expand distribution across East Africa",
                    "Invest in sustainable packaging and formulations",
                    "Build a stronger community of professional partners",
                  ].map((item) => (
                    <li key={item} className="flex items-start gap-3 text-[#475569]">
                      <span className="w-2 h-2 rounded-full bg-green-500 mt-2 flex-shrink-0" />
                      {item}
                    </li>
                  ))}
                </ul>
              </div>
              <div className="relative aspect-square rounded-[24px] bg-gradient-to-br from-green-500 to-green-600 overflow-hidden flex items-center justify-center p-8">
                <h3 className="text-3xl lg:text-5xl font-black text-white text-center tracking-tight">
                  The Future
                  <br />
                  of Fabric Care
                </h3>
              </div>
            </div>
          </Container>
        </section>

        {/* CTA */}
        <CTASection
          title="Partner with VESTRA"
          description="Join our growing network of distributors and bring professional fabric care to your community."
          buttonText="Become a Distributor"
          buttonHref="/distributor"
          secondaryButton={{ text: "Contact Us", href: "/contact" }}
          light
        />
      </main>
    </>
  );
}
