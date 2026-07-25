import { Metadata } from "next";
import { PreferencesPageClient } from "./preferences-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Preferences",
  description: "Manage your VESTRA account preferences and notifications.",
  pathname: "/account/preferences",
});

export default function PreferencesPage() {
  return <PreferencesPageClient />;
}
