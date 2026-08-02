"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { FileText, Loader2 } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";

export function QuotesPageClient() {
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
        title="My Quote Requests"
        subtitle="Track and manage your quote requests"
        breadcrumb={[{ label: "Account", href: "/account" }, { label: "My Quotes" }]}
      />

      <section className="py-12 lg:py-20 bg-surface-page">
        <Container>
          <div className="bg-surface-card rounded-[20px] border border-default shadow-sm p-6 lg:p-8">
            <div className="py-16 text-center">
              <div className="w-16 h-16 rounded-full bg-surface-page flex items-center justify-center mx-auto mb-5 border border-default">
                <FileText className="w-8 h-8 text-placeholder" />
              </div>
              <h3 className="text-lg font-bold text-text-heading mb-2">No quote requests yet</h3>
              <p className="text-muted mb-6 max-w-md mx-auto">
                Submit a quote request and it will appear here.
              </p>
              <Link
                href="/request-quote"
                className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
              >
                <FileText className="w-4 h-4" />
                Request a Quote
              </Link>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
