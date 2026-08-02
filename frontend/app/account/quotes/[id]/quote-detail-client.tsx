"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { ChevronLeft, AlertCircle, Loader2 } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

interface Props {
  id: number;
}

export function QuoteDetailPageClient({ id }: Props) {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading) {
    return (
      <div className="min-h-[50vh] flex items-center justify-center">
        <Loader2 className="w-8 h-8 animate-spin text-secondary-500" />
      </div>
    );
  }

  if (!isAuthenticated) return null;

  return (
    <>
      <PageHero
        title="Quote Request Details"
        breadcrumb={[
          { label: "Account", href: "/account" },
          { label: "My Quotes", href: "/account/quotes" },
          { label: `#${id}` },
        ]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <Link
            href="/account/quotes"
            className="inline-flex items-center gap-2 text-sm font-semibold text-muted hover:text-text-heading mb-6"
          >
            <ChevronLeft className="w-4 h-4" />
            Back to Quotes
          </Link>

          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm py-16 text-center">
            <AlertCircle className="w-14 h-14 mx-auto mb-4 text-placeholder" />
            <h2 className="text-lg font-bold text-text-heading mb-2">Quote not found</h2>
            <p className="text-muted mb-6 max-w-md mx-auto">
              The quote request you are looking for does not exist.
            </p>
            <Link
              href="/account/quotes"
              className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
            >
              <ChevronLeft className="w-4 h-4" />
              Back to Quotes
            </Link>
          </div>
        </Container>
      </section>
    </>
  );
}
