import { Metadata } from "next";
import { SettingsPageClient } from "./settings-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Account Settings",
  description: "Update your profile and password.",
  pathname: "/account/settings",
});

export default function SettingsPage() {
  return <SettingsPageClient />;
}
