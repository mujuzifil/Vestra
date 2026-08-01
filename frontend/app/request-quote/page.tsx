import { Metadata } from "next";
import { RequestQuotePageClient } from "./request-quote-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Request a Quote",
  description:
    "Request a tailored quotation for VESTRA detergents. Ideal for institutions, resale, distributors, and large-scale supply.",
  pathname: "/request-quote",
});

export default function RequestQuotePage() {
  return <RequestQuotePageClient />;
}
