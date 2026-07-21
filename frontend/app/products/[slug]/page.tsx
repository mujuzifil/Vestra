import { Metadata } from "next";
import { notFound } from "next/navigation";
import { getProductBySlug } from "@/lib/api/products";
import { createMetadata } from "@/lib/metadata";
import ProductPageClient from "./product-page-client";

interface ProductPageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: ProductPageProps): Promise<Metadata> {
  const { slug } = await params;
  const product = await getProductBySlug(slug);

  if (!product) {
    return createMetadata({
      title: "Product Not Found",
      description: "The requested product could not be found.",
      pathname: `/products/${slug}`,
    });
  }

  const title = product.meta_title
    ? product.meta_title.replace(/\s*\|\s*VESTRA$/i, '')
    : product.name;
  const description = product.meta_description || product.short_description || product.description;
  const image = product.images[0]?.image || "/assets/images/branding/vestra-logo.png";

  return createMetadata({
    title,
    description,
    pathname: `/products/${slug}`,
    image,
    keywords: [product.name, product.category?.name || "fabric care"],
  });
}

export default async function ProductPage({ params }: ProductPageProps) {
  const { slug } = await params;
  const product = await getProductBySlug(slug);

  if (!product) {
    notFound();
  }

  return <ProductPageClient slug={slug} />;
}
