import { Metadata } from "next";
import { SettingsPageClient } from "./settings-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Settings",
  description: "Distributor portal settings.",
  pathname: "/distributor/settings",
});

export default function SettingsPage() {
  return <SettingsPageClient />;
}
