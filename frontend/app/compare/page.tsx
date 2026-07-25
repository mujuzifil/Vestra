import { Metadata } from "next";
import { ComparePageClient } from "./compare-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Compare Products",
  description: "Compare VESTRA products side by side.",
  pathname: "/compare",
});

export default function ComparePage() {
  return <ComparePageClient />;
}
