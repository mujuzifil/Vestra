import { Metadata } from "next";
import { StatementsPageClient } from "./statements-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Statements",
  description: "View your distributor account statements.",
  pathname: "/distributor/statements",
});

export default function StatementsPage() {
  return <StatementsPageClient />;
}
