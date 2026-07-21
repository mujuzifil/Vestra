import { Metadata } from "next";
import { ContactPageClient } from "./contact-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Contact Us",
  description:
    "Get in touch with VESTRA for support, partnerships, distributor inquiries, and general questions.",
  keywords: ["contact", "support", "distributor inquiry", "Uganda"],
  pathname: "/contact",
});

export default function ContactPage() {
  return <ContactPageClient />;
}
