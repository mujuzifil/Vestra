import { Metadata } from "next";
import { RegisterPageClient } from "./register-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Register",
  description: "Create a VESTRA account to shop and track orders.",
  pathname: "/auth/register",
});

export default function RegisterPage() {
  return <RegisterPageClient />;
}
