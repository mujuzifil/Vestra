import { Metadata } from "next";
import { CheckoutPageClient } from "./checkout-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Checkout",
  description: "Complete your VESTRA order.",
  pathname: "/checkout",
});

export default function CheckoutPage() {
  return <CheckoutPageClient />;
}
