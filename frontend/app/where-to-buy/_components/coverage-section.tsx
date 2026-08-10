"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { CoverageMap } from "@/components/distributor/coverage-map";

export function CoverageSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="coverage-heading">
      <Container>
        <SectionHeader
          id="coverage-heading"
          title="Coverage across Uganda"
          subtitle="See where authorised VESTRA distributors are listed by region and district."
        />
        <CoverageMap />
      </Container>
    </section>
  );
}
