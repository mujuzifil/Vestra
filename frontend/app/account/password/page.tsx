import { Metadata } from "next";
import { PasswordPageClient } from "./password-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Change Password",
  description: "Update your VESTRA account password.",
  pathname: "/account/password",
});

export default function PasswordPage() {
  return <PasswordPageClient />;
}
