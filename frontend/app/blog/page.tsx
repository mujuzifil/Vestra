import { Metadata } from "next";
import { BlogPageClient } from "./blog-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Knowledge Centre",
  description:
    "Expert advice on laundry, stain removal, detergent science, fabric care and commercial cleaning solutions from VESTRA®.",
  pathname: "/blog",
});

export default function BlogPage() {
  return <BlogPageClient />;
}
