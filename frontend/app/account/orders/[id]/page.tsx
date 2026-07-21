import { Metadata } from "next";
import { OrderDetailPageClient } from "./order-detail-client";
import { createMetadata } from "@/lib/metadata";

interface Props {
  params: Promise<{ id: string }>;
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params;
  return createMetadata({
    title: `Order #${id}`,
    description: "View order details and tracking information.",
    pathname: `/account/orders/${id}`,
  });
}

export default async function OrderDetailPage({ params }: Props) {
  const { id } = await params;
  return <OrderDetailPageClient orderId={parseInt(id, 10)} />;
}
