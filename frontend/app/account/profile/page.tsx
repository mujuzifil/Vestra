import { Metadata } from "next";
import { ProfilePageClient } from "./profile-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Edit Profile",
  description: "Update your VESTRA profile information.",
  pathname: "/account/profile",
});

export default function ProfilePage() {
  return <ProfilePageClient />;
}
