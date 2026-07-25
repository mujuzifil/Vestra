import { Metadata } from "next";
import { WishlistPageClient } from "./wishlist-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Wishlist & Saved Items",
  description: "View your saved products and wishlist on VESTRA.",
  pathname: "/account/wishlist",
});

export default function WishlistPage() {
  return <WishlistPageClient />;
}
