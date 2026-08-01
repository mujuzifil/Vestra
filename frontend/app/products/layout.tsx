import { Metadata } from "next";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Professional Cleaning Products | VESTRA®",
  description:
    "Browse VESTRA® professional cleaning and fabric care products for commercial laundries, institutions, and distributors in Uganda.",
  keywords: [
    "products",
    "manufacturer",
    "detergent",
    "fabric care",
    "commercial cleaning",
    "institutional supply",
    "Uganda",
    "B2B",
    "distributor",
  ],
  pathname: "/products",
});

export default function ProductsLayout({ children }: { children: React.ReactNode }) {
  return <>{children}</>;
}
