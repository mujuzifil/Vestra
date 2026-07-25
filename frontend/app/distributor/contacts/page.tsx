import { Metadata } from "next";
import { ContactsPageClient } from "./contacts-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Contacts",
  description: "Manage your distributor contacts.",
  pathname: "/distributor/contacts",
});

export default function ContactsPage() {
  return <ContactsPageClient />;
}
