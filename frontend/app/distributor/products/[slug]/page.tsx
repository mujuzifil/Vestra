import { Metadata } from "next";
import { ProductDetailPageClient } from "./product-detail-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Product Details",
  description: "Distributor product details and pricing.",
  pathname: "/distributor/products",
});

interface Props {
  params: Promise<{ slug: string }>;
}

export default async function ProductDetailPage({ params }: Props) {
  const { slug } = await params;
  return <ProductDetailPageClient slug={slug} />;
}
