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
          title="Coverage Map"
          subtitle="Our authorised distributor coverage across Uganda."
        />
        <CoverageMap />
      </Container>
    </section>
  );
}
