import { Metadata } from "next";
import { TrackPageClient } from "./track-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Track Your Order",
  description: "Track your VESTRA order status and delivery progress.",
  pathname: "/track",
});

interface Props {
  searchParams: Promise<{ [key: string]: string | string[] | undefined }>;
}

export default async function TrackPage({ searchParams }: Props) {
  const params = await searchParams;
  const invoice = typeof params.invoice === "string" ? params.invoice : "";

  return <TrackPageClient initialInvoice={invoice} />;
}
