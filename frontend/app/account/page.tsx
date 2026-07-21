import { Metadata } from "next";
import { AccountPageClient } from "./account-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "My Account",
  description: "Manage your VESTRA account, orders, and addresses.",
  pathname: "/account",
});

export default function AccountPage() {
  return <AccountPageClient />;
}
