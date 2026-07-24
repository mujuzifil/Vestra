import { Metadata } from "next";
import { SecurityPageClient } from "./security-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Security",
  description: "Review your VESTRA account security and active sessions.",
  pathname: "/account/security",
});

export default function SecurityPage() {
  return <SecurityPageClient />;
}
