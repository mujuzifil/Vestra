import { Metadata } from "next";
import { BulkOrdersPageClient } from "./bulk-orders-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Bulk & Corporate Orders",
  description: "Request a wholesale quotation for VESTRA detergents. Ideal for institutions, resale, and large-scale supply.",
  pathname: "/bulk-orders",
});

export default function BulkOrdersPage() {
  return <BulkOrdersPageClient />;
}
