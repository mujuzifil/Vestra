import { Metadata } from "next";
import { ConfirmPageClient } from "./confirm-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Order Confirmed",
  description: "Your VESTRA order has been placed successfully.",
  pathname: "/checkout/confirm",
});

export default function ConfirmPage() {
  return <ConfirmPageClient />;
}
