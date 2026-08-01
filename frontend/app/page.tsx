import { Metadata } from "next";
import { HeroSection } from "@/components/sections/hero-section";
import { WhyChooseSection } from "@/components/sections/why-choose-section";
import { ProductCategoriesSection } from "@/components/sections/product-categories-section";
import { IndustriesSection } from "@/components/sections/industries-section";
import { FeaturedProductsSection } from "@/components/sections/featured-products-section";
import { ManufacturingSection } from "@/components/sections/manufacturing-section";
import { DistributorCtaSection } from "@/components/sections/distributor-cta-section";
import { RequestQuoteSection } from "@/components/sections/request-quote-section";
import { TestimonialsSection } from "@/components/sections/testimonials-section";
import { LatestArticlesSection } from "@/components/sections/latest-articles-section";
import { ContactBannerSection } from "@/components/sections/contact-banner-section";
import { createMetadata, siteConfig } from "@/lib/metadata";
import { JsonLd, organizationSchema, websiteSchema, manufacturerSchema } from "@/lib/structured-data";

export const metadata: Metadata = createMetadata({
  title: `${siteConfig.name}® | Professional Cleaning Solutions Manufactured for Uganda`,
  description:
    "VESTRA® is a Ugandan manufacturer of professional detergents and fabric care products. We supply businesses, institutions, and distributors across Uganda with quality cleaning solutions.",
  keywords: [
    "manufacturer",
    "cleaning solutions",
    "detergent",
    "fabric care",
    "Uganda",
    "B2B",
    "distributor",
    "institutional supply",
  ],
  pathname: "/",
  image: "/assets/images/branding/vestra-logo.png",
});

export default function Home() {
  return (
    <>
      <JsonLd data={organizationSchema()} />
      <JsonLd data={websiteSchema()} />
      <JsonLd data={manufacturerSchema()} />
      <main>
        <HeroSection />
        <WhyChooseSection />
        <ProductCategoriesSection />
        <IndustriesSection />
        <FeaturedProductsSection />
        <ManufacturingSection />
        <DistributorCtaSection />
        <RequestQuoteSection />
        <TestimonialsSection />
        <LatestArticlesSection />
        <ContactBannerSection />
      </main>
    </>
  );
}
