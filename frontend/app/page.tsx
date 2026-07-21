import { Metadata } from "next";
import { HeroSection } from "@/components/sections/hero-section";
import { BrandIntroSection } from "@/components/sections/brand-intro-section";
import { PromiseSection } from "@/components/sections/promise-section";
import { WhyChooseSection } from "@/components/sections/why-choose-section";
import { FeaturedProductsSection } from "@/components/sections/featured-products-section";
import { VisionBannerSection } from "@/components/sections/vision-banner-section";
import { DistributorCtaSection } from "@/components/sections/distributor-cta-section";
import { createMetadata, siteConfig } from "@/lib/metadata";
import { JsonLd, organizationSchema, websiteSchema } from "@/lib/structured-data";

export const metadata: Metadata = createMetadata({
  title: siteConfig.name,
  description: siteConfig.description,
  keywords: ["home", "fabric care", "premium detergent", "Uganda"],
  pathname: "/",
});

export default function Home() {
  return (
    <>
      <JsonLd data={organizationSchema()} />
      <JsonLd data={websiteSchema()} />
      <main>
        <HeroSection />
        <BrandIntroSection />
        <PromiseSection />
        <WhyChooseSection />
        <FeaturedProductsSection />
        <VisionBannerSection />
        <DistributorCtaSection />
      </main>
    </>
  );
}
