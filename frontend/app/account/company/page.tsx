import { Metadata } from "next";
import { CompanyPageClient } from "./company-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Company Information",
  description: "View your company and contact details on VESTRA.",
  pathname: "/account/company",
});

export default function CompanyPage() {
  return <CompanyPageClient />;
}
