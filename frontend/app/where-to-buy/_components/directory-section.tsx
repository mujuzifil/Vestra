"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { DirectoryList } from "@/components/distributor/directory-list";

interface DirectorySectionProps {
  contactPhone: string;
  contactEmail: string;
}

export function DirectorySection({ contactPhone, contactEmail }: DirectorySectionProps) {
  return (
    <section id="directory" className="py-20 lg:py-28 bg-neutral-50" aria-labelledby="directory-heading">
      <Container>
        <SectionHeader
          id="directory-heading"
          title="Authorized Distributor Directory"
          subtitle="Shop with confidence from an Authorized VESTRA Distributor. Search by name, district, area, tier, or stock availability."
        />
        <DirectoryList contactPhone={contactPhone} contactEmail={contactEmail} />
      </Container>
    </section>
  );
}
