import { Metadata } from "next";
import { OrderDetailPageClient } from "./order-detail-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Order Details",
  description: "View distributor order details.",
  pathname: "/distributor/orders",
});

interface Props {
  params: Promise<{ id: string }>;
}

export default async function OrderDetailPage({ params }: Props) {
  const { id } = await params;
  return <OrderDetailPageClient orderId={Number(id)} />;
}
