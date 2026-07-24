import { Metadata } from "next";
import { InvoiceDetailPageClient } from "./invoice-detail-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Invoice Details",
  description: "View distributor invoice details.",
  pathname: "/distributor/invoices",
});

interface Props {
  params: Promise<{ id: string }>;
}

export default async function InvoiceDetailPage({ params }: Props) {
  const { id } = await params;
  return <InvoiceDetailPageClient invoiceId={Number(id)} />;
}
