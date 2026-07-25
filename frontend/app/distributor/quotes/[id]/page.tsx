import { Metadata } from "next";
import { QuoteDetailPageClient } from "./quote-detail-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Quote Details",
  description: "View distributor quote details.",
  pathname: "/distributor/quotes",
});

interface Props {
  params: Promise<{ id: string }>;
}

export default async function QuoteDetailPage({ params }: Props) {
  const { id } = await params;
  return <QuoteDetailPageClient quoteId={Number(id)} />;
}
