import { Metadata } from "next";
import { AboutPageClient } from "./about-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "About VESTRA® | Ugandan Detergent Manufacturer",
  description:
    "Learn about VESTRA®, a Ugandan manufacturer of professional detergents and cleaning solutions. Discover our mission, values, manufacturing capability, and partnership opportunities.",
  keywords: [
    "about",
    "manufacturer",
    "Uganda",
    "detergent",
    "fabric care",
    "cleaning solutions",
    "B2B",
    "distributor",
    "mission",
    "vision",
    "values",
  ],
  pathname: "/about",
});

export default function AboutPage() {
  return <AboutPageClient />;
}
