import { Metadata } from "next";
import { ProductsPageClient } from "./products-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Distributor Products",
  description: "Browse VESTRA products with distributor pricing.",
  pathname: "/distributor/products",
});

export default function ProductsPage() {
  return <ProductsPageClient />;
}
