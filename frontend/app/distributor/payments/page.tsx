import { Metadata } from "next";
import { PaymentsPageClient } from "./payments-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Payments",
  description: "Submit and track distributor payments.",
  pathname: "/distributor/payments",
});

export default function PaymentsPage() {
  return <PaymentsPageClient />;
}
