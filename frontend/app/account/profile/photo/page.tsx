import { Metadata } from "next";
import { PhotoPageClient } from "./photo-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Profile Photo",
  description: "Upload or remove your VESTRA profile photo.",
  pathname: "/account/profile/photo",
});

export default function PhotoPage() {
  return <PhotoPageClient />;
}
