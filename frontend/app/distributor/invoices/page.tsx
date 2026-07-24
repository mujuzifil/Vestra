import { Metadata } from "next";
import { InvoicesPageClient } from "./invoices-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Invoices",
  description: "View your distributor invoices.",
  pathname: "/distributor/invoices",
});

export default function InvoicesPage() {
  return <InvoicesPageClient />;
}
