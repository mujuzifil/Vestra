import { Metadata } from "next";
import Link from "next/link";
import { Container } from "@/components/common/container";
import { Button } from "@/components/ui/button";
import { ArrowLeft } from "lucide-react";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Page Not Found",
  description: "The page you are looking for does not exist. Return to VESTRA's homepage.",
  pathname: "/404",
});

export default function NotFoundPage() {
  return (
    <main className="min-h-screen flex items-center justify-center bg-surface-page pt-24 pb-16">
      <Container className="text-center">
        <h1 className="text-7xl lg:text-9xl font-black text-text-heading mb-4">404</h1>
        <h2 className="text-2xl lg:text-4xl font-bold text-text-heading mb-4">Page Not Found</h2>
        <p className="text-text-muted text-base lg:text-lg max-w-md mx-auto mb-8">
          The page you are looking for might have been moved, deleted, or never existed.
        </p>
        <Button asChild variant="gradient" className="rounded-full px-7 py-3.5 h-auto" leftIcon={<ArrowLeft className="w-4 h-4" />}>
          <Link href="/">Back to Home</Link>
        </Button>
      </Container>
    </main>
  );
}
