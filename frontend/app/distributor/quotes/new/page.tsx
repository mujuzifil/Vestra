import { Metadata } from "next";
import { NewQuoteClient } from "./new-quote-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "New Quote",
  description: "Create a new distributor quotation request.",
  pathname: "/distributor/quotes/new",
});

export default function NewQuotePage() {
  return <NewQuoteClient />;
}
