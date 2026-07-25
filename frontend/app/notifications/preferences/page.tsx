import { Metadata } from "next";
import { NotificationPreferencesPageClient } from "./notification-preferences-page-client";

export const metadata: Metadata = {
  title: "Notification Preferences | VESTRA",
  description: "Manage your VESTRA notification preferences.",
};

export default function NotificationPreferencesPage() {
  return <NotificationPreferencesPageClient />;
}
