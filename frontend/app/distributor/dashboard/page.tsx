import { Metadata } from "next";
import { DashboardPageClient } from "./dashboard-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Distributor Dashboard",
  description: "Overview of your VESTRA distributor account.",
  pathname: "/distributor/dashboard",
});

export default function DashboardPage() {
  return <DashboardPageClient />;
}
