import { Metadata } from "next";
import { SavedProductsPageClient } from "./saved-products-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Saved Products",
  description: "View and manage your saved VESTRA products.",
  pathname: "/account/saved-products",
});

export default function SavedProductsPage() {
  return <SavedProductsPageClient />;
}
