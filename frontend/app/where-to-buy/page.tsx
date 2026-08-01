import { Metadata } from "next";
import { WhereToBuyPageClient } from "./where-to-buy-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Where to Buy",
  description:
    "Find authorised VESTRA distributors and retailers, or contact our sales team for direct supply and partnership enquiries.",
  pathname: "/where-to-buy",
});

export default function WhereToBuyPage() {
  return <WhereToBuyPageClient />;
}
