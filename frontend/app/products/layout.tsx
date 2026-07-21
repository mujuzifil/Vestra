import { Metadata } from "next";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Our Products",
  description:
    "Browse VESTRA's professional fabric care products including detergents, delicate care, specialty care, finishing, and stain removal solutions.",
  keywords: ["products", "detergent", "fabric care", "laundry", "cleaning"],
  pathname: "/products",
});

export default function ProductsLayout({ children }: { children: React.ReactNode }) {
  return <>{children}</>;
}
