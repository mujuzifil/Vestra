"use client";

import { useEffect } from "react";
import Link from "next/link";
import { Container } from "@/components/common/container";
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
        <h1 className="text-3xl lg:text-4xl font-bold text-primary-900 mb-4">
          Something went wrong
        </h1>
        <p className="text-muted text-base lg:text-lg mb-8 leading-relaxed">
          We apologize for the inconvenience. An unexpected error occurred while loading this page.
        </p>
        <div className="flex flex-wrap justify-center gap-4">
          <button
            onClick={reset}
            className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-white bg-gradient-to-br from-green-500 to-green-600 shadow-lg hover:-translate-y-1 transition-transform-base"
          >
            <RotateCcw className="w-4 h-4" />
            Try Again
          </button>
          <Link
            href="/"
            className="inline-flex items-center gap-2 px-6 py-3 rounded-full font-semibold text-primary-900 border border-default bg-white hover:bg-surface-page hover:-translate-y-1 transition-all-base"
          >
            <Home className="w-4 h-4" />
            Back to Home
          </Link>
        </div>
      </Container>
    </main>
  );
}
