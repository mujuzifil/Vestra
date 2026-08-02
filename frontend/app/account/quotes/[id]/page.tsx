import { Metadata } from "next";
import { QuoteDetailPageClient } from "./quote-detail-client";
import { createMetadata } from "@/lib/metadata";

interface Props {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params;
  return createMetadata({
    title: `Quote Request #${id}`,
    description: "View your VESTRA quote request details.",
    pathname: `/account/quotes/${id}`,
  });
}

export default async function QuoteDetailPage({ params }: Props) {
  const { id } = await params;
  return <QuoteDetailPageClient id={parseInt(id, 10)} />;
}
