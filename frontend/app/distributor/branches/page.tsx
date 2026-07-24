import { Metadata } from "next";
import { BranchesPageClient } from "./branches-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Branches",
  description: "Manage your distributor branches.",
  pathname: "/distributor/branches",
});

export default function BranchesPage() {
  return <BranchesPageClient />;
}
