import { Metadata } from "next";
import { AboutPageClient } from "./about-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "About Us",
  description:
    "Learn about VESTRA's mission, vision, core values, and commitment to premium fabric care across Africa.",
  keywords: ["about", "mission", "vision", "values", "company"],
  pathname: "/about",
});

export default function AboutPage() {
  return <AboutPageClient />;
}
