import { Metadata } from "next";
import { QuotesPageClient } from "./quotes-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "My Quotes",
  description: "View and manage your VESTRA quote requests.",
  pathname: "/account/quotes",
});

export default function QuotesPage() {
  return <QuotesPageClient />;
}
