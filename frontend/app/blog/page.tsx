import { Metadata } from "next";
import { BlogPageClient } from "./blog-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Blog",
  description:
    "Insights, product knowledge, and industry updates from VESTRA — premium fabric care solutions for professionals.",
  pathname: "/blog",
});

export default function BlogPage() {
  return <BlogPageClient />;
}
