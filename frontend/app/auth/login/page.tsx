import { Metadata } from "next";
import { LoginPageClient } from "./login-page-client";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Login",
  description: "Sign in to your VESTRA account to manage orders and saved addresses.",
  pathname: "/auth/login",
});

export default function LoginPage() {
  return <LoginPageClient />;
}
