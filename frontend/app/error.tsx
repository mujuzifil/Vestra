"use client";

import { useEffect } from "react";
import Link from "next/link";
import { Container } from "@/components/common/container";
import { Button } from "@/components/ui/button";
import { AlertTriangle, RotateCcw, Home } from "lucide-react";

interface ErrorBoundaryProps {
  error: Error & { digest?: string };
  reset: () => void;
}

export default function ErrorBoundary({ error, reset }: ErrorBoundaryProps) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <main className="min-h-screen flex items-center justify-center bg-surface-page pt-24 pb-16">
      <Container className="text-center max-w-xl">
        <div className="w-20 h-20 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-6">
          <AlertTriangle className="w-10 h-10" />
        </div>
        <h1 className="text-3xl lg:text-4xl font-bold text-text-heading mb-4">
          Something went wrong
        </h1>
        <p className="text-text-muted text-base lg:text-lg mb-8 leading-relaxed">
          We apologize for the inconvenience. An unexpected error occurred while loading this page.
        </p>
        <div className="flex flex-wrap justify-center gap-4">
          <Button variant="gradient" className="rounded-full px-6 py-3 h-auto" leftIcon={<RotateCcw className="w-4 h-4" />} onClick={reset}>
            Try Again
          </Button>
          <Button asChild variant="outline" className="rounded-full px-6 py-3 h-auto">
            <Link href="/"><Home className="w-4 h-4" aria-hidden="true" /> Back to Home</Link>
          </Button>
        </div>
      </Container>
    </main>
  );
}
