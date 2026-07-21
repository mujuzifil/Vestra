import { Metadata } from "next";
import { DistributorPageClient } from "./distributor-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Become a Distributor",
  description:
    "Partner with VESTRA and join our growing network of professional fabric care distributors across Uganda and East Africa.",
  keywords: ["distributor", "partnership", "wholesale", "business opportunity"],
  pathname: "/distributor",
});

export default function DistributorPage() {
  return <DistributorPageClient />;
}
