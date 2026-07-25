import { Metadata } from "next";
import { ReviewsPageClient } from "./reviews-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "My Reviews",
  description: "View and manage your VESTRA product reviews.",
  pathname: "/account/reviews",
});

export default function ReviewsPage() {
  return <ReviewsPageClient />;
}
