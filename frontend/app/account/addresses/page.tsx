import { Metadata } from "next";
import { AddressesPageClient } from "./addresses-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Business Addresses",
  description: "Manage your business delivery addresses.",
  pathname: "/account/addresses",
});

export default function AddressesPage() {
  return <AddressesPageClient />;
}
