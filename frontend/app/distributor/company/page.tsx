import { Metadata } from "next";
import { CompanyPageClient } from "./company-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Company Profile",
  description: "Manage your distributor company profile.",
  pathname: "/distributor/company",
});

export default function CompanyPage() {
  return <CompanyPageClient />;
}
