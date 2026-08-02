import { Metadata } from "next";
import { DocumentsPageClient } from "./documents-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Documents",
  description: "Access your VESTRA quotations, certificates, and catalogues.",
  pathname: "/account/documents",
});

export default function DocumentsPage() {
  return <DocumentsPageClient />;
}
