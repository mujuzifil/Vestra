"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { FileText, Loader2, ArrowRight, Eye } from "lucide-react";
import { Container } from "@/components/common/container";
import { PageHero } from "@/components/common/page-hero";
import { useAuth } from "@/lib/auth-context";
import { useAccountQuotes } from "@/hooks/use-account-quotes";
import { Badge } from "@/components/ui/badge";

function statusVariant(status: string): "default" | "secondary" | "outline" | "danger" {
  switch (status) {
    case "approved":
    case "quoted":
      return "default";
    case "pending":
      return "secondary";
    case "rejected":
      return "danger";
    default:
      return "outline";
  }
}

export function QuotesPageClient() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuth();
  const { data, isLoading: quotesLoading } = useAccountQuotes(1);
  const quotes = data?.data ?? [];

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push("/auth/login");
    }
  }, [authLoading, isAuthenticated, router]);

  if (authLoading || quotesLoading) {
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
            {quotes.length === 0 ? (
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
            ) : (
              <div className="space-y-4">
                {quotes.map((quote) => (
                  <div
                    key={quote.id}
                    className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-default bg-surface-page"
                  >
                    <div className="min-w-0">
                      <div className="flex items-center gap-2 flex-wrap mb-1">
                        <span className="font-semibold text-text-heading">{quote.reference_number}</span>
                        <Badge variant={statusVariant(quote.status)}>{quote.status_label}</Badge>
                      </div>
                      <p className="text-sm text-muted">
                        {quote.company_name || "No company name"} • {quote.items.length} product
                        {quote.items.length !== 1 ? "s" : ""} • Submitted{" "}
                        {new Date(quote.created_at).toLocaleDateString()}
                      </p>
                    </div>
                    <Link
                      href={`/account/quotes/${quote.id}`}
                      className="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-secondary-600 bg-secondary-50 rounded-xl hover:bg-secondary-100 transition-colors-base"
                    >
                      <Eye className="w-4 h-4" />
                      View Details
                    </Link>
                  </div>
                ))}
              </div>
            )}

            {quotes.length > 0 && (
              <div className="mt-8 pt-6 border-t border-default text-center">
                <Link
                  href="/request-quote"
                  className="inline-flex items-center gap-2 px-6 py-3 bg-secondary-600 text-white font-semibold rounded-xl hover:opacity-90"
                >
                  <FileText className="w-4 h-4" />
                  Request Another Quote
                  <ArrowRight className="w-4 h-4" />
                </Link>
              </div>
            )}
          </div>
        </Container>
      </section>
    </>
  );
}
