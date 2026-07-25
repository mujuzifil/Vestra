import { Metadata } from "next";
import { RecentlyViewedPageClient } from "./recently-viewed-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Recently Viewed",
  description: "Products you have recently viewed on VESTRA.",
  pathname: "/account/recently-viewed",
});

export default function RecentlyViewedPage() {
  return <RecentlyViewedPageClient />;
}
