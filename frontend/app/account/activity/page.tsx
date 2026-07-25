import { Metadata } from "next";
import { ActivityPageClient } from "./activity-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Account Activity",
  description: "Review recent activity on your VESTRA account.",
  pathname: "/account/activity",
});

export default function ActivityPage() {
  return <ActivityPageClient />;
}
