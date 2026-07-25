import { Metadata } from "next";
import { NotificationsPageClient } from "./notifications-page-client";

export const metadata: Metadata = {
  title: "Notifications | VESTRA",
  description: "View and manage your VESTRA notifications.",
};

export default function NotificationsPage() {
  return <NotificationsPageClient />;
}
