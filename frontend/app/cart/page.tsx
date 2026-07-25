import { Metadata } from "next";
import { CartPageClient } from "./cart-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Shopping Cart",
  description: "Review your VESTRA cart, update quantities, and proceed to checkout.",
  pathname: "/cart",
});

export default function CartPage() {
  return <CartPageClient />;
}
