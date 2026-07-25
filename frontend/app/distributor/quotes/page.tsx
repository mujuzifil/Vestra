import { Metadata } from "next";
import { QuotesPageClient } from "./quotes-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Quotes",
  description: "Manage your distributor quotation requests.",
  pathname: "/distributor/quotes",
});

export default function QuotesPage() {
  return <QuotesPageClient />;
}
