import { Metadata } from "next";
import { AnalyticsPageClient } from "./analytics-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Analytics",
  description: "Distributor performance analytics.",
  pathname: "/distributor/analytics",
});

export default function AnalyticsPage() {
  return <AnalyticsPageClient />;
}
