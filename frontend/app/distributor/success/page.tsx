import { Metadata } from "next";
import { DistributorSuccessPageClient } from "./success-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Distributor Application Submitted",
  description: "Your VESTRA distributor application has been received. Our team will review it within 5-7 business days.",
  pathname: "/distributor/success",
});

export default function DistributorSuccessPage() {
  return <DistributorSuccessPageClient />;
}
