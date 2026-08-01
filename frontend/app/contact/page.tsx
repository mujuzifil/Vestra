import { Metadata } from "next";
import { ContactPageClient } from "./contact-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Contact VESTRA®",
  description:
    "Contact VESTRA® for sales, distributor opportunities, technical support, and general enquiries. Call, WhatsApp, email, or visit our office in Kampala, Uganda.",
  keywords: ["contact", "sales", "support", "distributor inquiry", "Kampala", "Uganda"],
  pathname: "/contact",
});

export default function ContactPage() {
  return <ContactPageClient />;
}
