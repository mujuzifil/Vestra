"use client";

import { Container } from "@/components/common/container";
import { SectionHeader } from "@/components/common/section-header";
import { ValueCard } from "@/components/common/value-card";

const requesterTypes = [
  { icon: "Hotel", title: "Hotels", description: "Housekeeping and laundry supply programmes." },
  { icon: "Stethoscope", title: "Hospitals", description: "Reliable hygiene products for healthcare facilities." },
  { icon: "School", title: "Schools", description: "Cleaning supplies for classrooms, dormitories, and kitchens." },
  { icon: "Factory", title: "Commercial Laundries", description: "Industrial detergent volumes and scheduled deliveries." },
  { icon: "Droplets", title: "Cleaning Companies", description: "Professional products for service providers." },
  { icon: "Building2", title: "Manufacturers", description: "Plant and facility cleaning solutions." },
  { icon: "HeartHandshake", title: "NGOs", description: "Programme supply for community and aid projects." },
  { icon: "Landmark", title: "Government", description: "Tender and institutional procurement." },
  { icon: "Store", title: "Supermarkets", description: "Retail and wholesale shelf stock." },
  { icon: "Warehouse", title: "Wholesale Businesses", description: "Volume orders for redistribution." },
  { icon: "Briefcase", title: "Corporate Offices", description: "Office and facilities management supply." },
  { icon: "FlaskConical", title: "Industrial Facilities", description: "Heavy-duty and specialty cleaning products." },
];

export function WhoCanRequestSection() {
  return (
    <section className="py-20 lg:py-28 bg-white" aria-labelledby="who-can-request-heading">
      <Container>
        <SectionHeader
          id="who-can-request-heading"
          title="Who Can Request a Quote"
          subtitle="VESTRA® supplies organisations of every size across Uganda."
        />
        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {requesterTypes.map((item, index) => (
            <ValueCard
              key={item.title}
              icon={item.icon}
              title={item.title}
              description={item.description}
              index={index}
            />
          ))}
        </div>
      </Container>
    </section>
  );
}
