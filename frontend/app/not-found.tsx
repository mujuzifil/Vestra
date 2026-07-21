import { Metadata } from "next";
import Link from "next/link";
import { Container } from "@/components/common/container";
import { ArrowLeft } from "lucide-react";
import { createMetadata } from "@/lib/metadata";

export const metadata: Metadata = createMetadata({
  title: "Page Not Found",
  description: "The page you are looking for does not exist. Return to VESTRA's homepage.",
  pathname: "/404",
});

export default function NotFoundPage() {
  return (
    <main className="min-h-screen flex items-center justify-center bg-[#f8fafc] pt-24 pb-16">
      <Container className="text-center">
        <h1 className="text-7xl lg:text-9xl font-black text-[#0a1628] mb-4">404</h1>
        <h2 className="text-2xl lg:text-4xl font-bold text-[#0a1628] mb-4">Page Not Found</h2>
        <p className="text-[#64748b] text-base lg:text-lg max-w-md mx-auto mb-8">
          The page you are looking for might have been moved, deleted, or never existed.
        </p>
        <Link
          href="/"
          className="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30 hover:-translate-y-1 transition-transform"
        >
          <ArrowLeft className="w-4 h-4" />
          Back to Home
        </Link>
      </Container>
    </main>
  );
}
