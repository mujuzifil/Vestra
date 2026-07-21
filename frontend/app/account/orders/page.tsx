import { Metadata } from "next";
import { OrdersPageClient } from "./orders-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "My Orders",
  description: "View and track your VESTRA orders.",
  pathname: "/account/orders",
});

export default function OrdersPage() {
  return <OrdersPageClient />;
}
