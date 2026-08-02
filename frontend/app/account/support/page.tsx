import { Metadata } from "next";
import { SupportPageClient } from "./support-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Support Centre",
  description: "Get help and contact the VESTRA support team.",
  pathname: "/account/support",
});

export default function SupportPage() {
  return <SupportPageClient />;
}
