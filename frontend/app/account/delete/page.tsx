import { Metadata } from "next";
import { DeletePageClient } from "./delete-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Delete Account",
  description: "Request deletion of your VESTRA account.",
  pathname: "/account/delete",
});

export default function DeletePage() {
  return <DeletePageClient />;
}
