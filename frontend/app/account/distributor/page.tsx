import { Metadata } from "next";
import { DistributorPageClient } from "./distributor-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Distributor Application",
  description: "Check the status of your VESTRA distributor application.",
  pathname: "/account/distributor",
});

export default function DistributorPage() {
  return <DistributorPageClient />;
}
