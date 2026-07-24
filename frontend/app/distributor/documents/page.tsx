import { Metadata } from "next";
import { DocumentsPageClient } from "./documents-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Documents",
  description: "Manage your distributor documents.",
  pathname: "/distributor/documents",
});

export default function DocumentsPage() {
  return <DocumentsPageClient />;
}
