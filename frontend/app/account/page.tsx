import { Metadata } from "next";
import { AccountPageClient } from "./account-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Business Portal",
  description: "Manage your VESTRA business account, quote requests, and company information.",
  pathname: "/account",
});

export default function AccountPage() {
  return <AccountPageClient />;
}
